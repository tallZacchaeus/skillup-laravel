# Project Scope — Shopping Cart & Multi-Item Checkout

**Status:** scoped & decisions confirmed (2026-07-29) — ready to build, not yet started
**Owner:** _TBD_
**Estimated effort:** ~2–3 weeks (see phased plan)

### Confirmed decisions (2026-07-29)
- **D1 Installments:** single-item only — installments offered only when the cart holds exactly one installment-eligible course; multi-course carts are full-payment.
- **D2 Entry model:** keep **both** "Add to cart" and a direct "Buy now" (single-item path reused).
- **D3 Guest cart:** session cart, merged into the user's DB cart on login.
- **D4 Discounts:** per-item redemptions + order-level `minimum_order_amount` on subtotal (engineering default).
- **D5 Pricing:** snapshot at add-time, re-validate against live price at checkout (engineering default).
- **Capacity/oversell:** **block at checkout** — re-check `enrollment_cap` before payment; if a seat is gone, block that item and ask the learner to adjust. No overselling, no refunds path needed for v1.

## 1. Objective

Let a learner add multiple catalogue courses to a cart and pay for them in a
**single checkout / single Paystack transaction**, instead of today's one-course-per-order flow.

### Success criteria
- A learner can add/remove multiple published courses to a cart (guest or logged-in) and see a running total.
- One checkout collects customer details once and initializes **one** Paystack payment for the order total.
- On payment, **all** items provision enrollments + Moodle access (already works — see §3).
- Discounts apply correctly across a multi-item order.
- Existing single-course "Buy now" and the **Programs guardian funnel** keep working unchanged.
- Fully test-covered; no regression to the current 137-test suite.

## 2. Current state (grounded)

**Already multi-item-capable (no change needed):**
- `Order` carries order-level `subtotal / discount_total / tax_total / total / amount_paid / balance_due` and `hasMany` `OrderItem` (`quantity, unit_amount, discount_amount, total, metadata` snapshot). — `CheckoutOrderService.php`, `Order`/`OrderItem`.
- `CheckoutController::formatOrder()` already renders `$order->items` as a list.
- `PaymentService::applySuccessfulPayment()` is idempotent (webhook `payload_hash` + `event_key` dedupe, `Paid`-guard, `lockForUpdate`), and `createPendingEnrollments()` **loops order items** → multi-item enrollment + `EnrollUserInMoodleJob` already fan out per item.
- Guest checkout already creates/links a learner account on payment (S3 fix, `PaymentService::resolveOrderUser`).
- `DiscountRedemption` already has **both** `order_id` and `product_id` → a per-item discount is representable today.

**Single-item constraints to remove:**
- `CheckoutOrderService::create(Product $product, array $data)` — builds an order from exactly one product; `subtotal = single price`; creates one `OrderItem`.
- Discounts locked against a single product; installment schedule bound to one `ProductPaymentPlan`.
- Entry UX is per-product: `/checkout/{product:slug}` → `Details.jsx` (one course), "Start enrollment" links straight to it.
- No cart concept, no cart page, no add-to-cart action.

## 3. In scope (v1) / Out of scope

**In scope**
- Session-backed guest cart + DB cart for authenticated users, merged on login.
- Add-to-cart / remove / cart page / nav cart indicator (mirrors the wishlist pattern just shipped).
- Multi-item order creation (`createFromCart`) + a cart-based checkout (customer step → review → one Paystack payment).
- Multi-item discount handling (per-item redemptions + order-level codes).
- Guards: catalogue-only, no duplicates, already-enrolled, single currency, published-only, capacity.

**Out of scope (v1) — deferred with reason**
- **Installments on multi-item carts** — installments stay single-item only (recommended; see §5.1). Order-level installment plans are a large, separate effort.
- **Programs in the cart** — programs keep their bespoke guardian/seat-hold funnel; program-backed products are never addable to the cart.
- **Free (₦0) instant-enroll** — depends on a separate free-course decision (no ₦0 products exist yet).
- **Corporate/bulk seat purchasing** — separate corporate flow.
- **Quantity > 1 of the same course** — enrollment is per-user; cart holds a set of distinct courses (qty always 1).

## 4. Key design decisions (recommendations — confirm before build)

| # | Decision | Recommended default | Alternative |
|---|---|---|---|
| D1 | Installments with multiple items | **Single-item only** — offer installments only when the cart has exactly one installment-eligible course; multi-item carts are full-payment | Full order-level installment engine (large) |
| D2 | Entry model | **Keep both** — "Add to cart" *and* a direct "Buy now" (single-item, reuses today's path) | Cart-only (remove buy-now) |
| D3 | Guest cart | **Session cart**, merged into the user's DB cart on login | Require login before add-to-cart |
| D4 | Discount scope | **Per-item redemptions** (one `DiscountRedemption` per eligible item) + order-level `minimum_order_amount` evaluated on order subtotal | Order-level single redemption (loses per-course targeting) |
| D5 | Price snapshotting | Snapshot `unit_amount` into `cart_items` at add-time, **re-validate against live `defaultPrice` at checkout** (warn on change) | Trust cart price (risk: stale/￧manipulated) |

## 5. Design detail

### 5.1 Data model changes
- **`carts`**: `id, user_id (nullable, FK), session_id (nullable, index), timestamps`. One active cart per user or per guest session.
- **`cart_items`**: `id, cart_id (FK cascade), product_id (FK cascade), unit_amount (snapshot), timestamps, unique(cart_id, product_id)`.
- Models: `Cart` (`items()`, `user()`), `CartItem` (`product()`); `User::cart()`.
- No changes to `orders` / `order_items` / `discount_redemptions` schema (already sufficient). Possibly add `discount_redemptions.order_item_id` (nullable) later for precise item↔redemption linkage — **optional**, not required for v1 since `(order_id, product_id)` is unique enough.

### 5.2 Backend
- **`CartService`**: resolve current cart (user or session), `add(Product)`, `remove(Product)`, `items()`, `clear()`, `mergeGuestIntoUser()` (call from the login `Authenticated` event / listener). Enforces guards (catalogue-only, not-a-program, published, single currency, not-already-enrolled, no-duplicate).
- **`CheckoutOrderService::createFromCart(Cart $cart, array $customer, array $opts)`** (new; keep `create(Product,...)` for buy-now):
  - Re-validate each item's live `defaultPrice`; reject/refresh on change.
  - Sum `subtotal`; create `Order`; create one `OrderItem` per cart item (snapshot track/level/cohort like today).
  - Apply discounts (§5.3); set `discount_total` and per-item `discount_amount/total`; recompute `total`, `balance_due`.
  - Capacity guard per item (see §6); create invoice; clear the cart; return order.
- **`WishlistController`-style `CartController`**: `index` (cart page), `add`, `remove`, `checkout` (customer step → `createFromCart` → redirect to existing `checkout.orders.review`).
- **Reuse unchanged:** `PaymentService::initializePaystack` (already pays `payableAmount` = full balance for non-installment orders), verify, `applySuccessfulPayment`, `createPendingEnrollments`, Moodle jobs, receipts, invoices.
- **Shared Inertia prop** `cart {count, ids}` (mirror the wishlist prop) for the nav badge + "in cart" state on cards.

### 5.3 Discounts (multi-item) — the hard part
`DiscountEligibilityService::validate()` / `lockForCheckout()` are per-`(product, amount)` and already write a redemption per `(order, product)`. Plan:
- For an **auto/public** discount: evaluate per item; lock a redemption for each eligible item.
- For a **code**: validate the code against each item's product; lock redemptions for the items it targets (track/product/level/cohort scope already supported); reject if it targets none.
- `minimum_order_amount`: evaluate against the **order subtotal**, not the item amount (small change to how `$amount` is passed).
- `usage_limit / per_email_limit / per_user_limit`: decide whether one code applied to N items counts as N redemptions or 1 — **recommend: 1 redemption per eligible item, but count as a single code use per order** (needs a small guard in the limit check).
- Stackability & installment-compatibility already modeled on `DiscountRule`.
- On payment success/cancel, `markRedeemed`/`release` already run per redemption → loops naturally.

### 5.4 Frontend
- **Add-to-cart** buttons on catalogue cards (`Courses/Index.jsx`), course detail (`Product.jsx`), and home featured cards — with an "In cart ✓ / Go to cart" state from the shared `cart` prop. Keep a secondary **Buy now**.
- **`/cart` page** (`Public/Cart.jsx`): line items, remove, subtotal, "Proceed to checkout", empty state, "you're already enrolled" notices.
- **Cart indicator** in `PublicLayout` nav (badge with count — same pattern as the wishlist heart just added).
- **Cart checkout**: reuse/adapt `Checkout/Details.jsx` to take the cart (customer info once) → existing `Review.jsx` / `Status.jsx` already render multi-item orders.
- Program products show **no** add-to-cart (they route to `/programs/...`).

## 6. Edge cases & guards
- Adding a course already in the cart → no-op (unique constraint) / toast.
- Adding a course the user is **already enrolled in** → block with a clear message.
- Mixed currency in cart → block (v1 is single-currency NGN).
- Program-backed product → not addable (route to program).
- Unpublished/removed product between add and checkout → drop with notice.
- Price changed between add and checkout → re-price + warn.
- **Capacity:** re-check each item's `enrollment_cap` at order creation (best-effort; there's an inherent race until payment — mitigate by re-checking in `createPendingEnrollments` and surfacing "sold out" if a seat vanished, refund/hold policy TBD).
- Empty cart at checkout → redirect to catalogue.

## 7. Security
- Cart is server-authoritative: **never trust client-sent prices**; always recompute from live `ProductPrice` at order creation.
- Rate-limit add/remove/checkout (mirror the `throttle:` already on wishlist/leads/discount routes).
- Guest→user cart merge must not leak another session's cart (scope by `session_id` + regenerate on login).
- Discount abuse: existing usage/email/user limits apply; ensure multi-item can't multiply a single-use code beyond intent (§5.3).
- Keep the Paystack webhook idempotency guarantees (unchanged).

## 8. Testing plan
- Cart: add/remove/dedupe, guest→user merge, guards (program/enrolled/currency).
- `createFromCart`: totals, per-item snapshots, price-change re-validation.
- Multi-item discount: code targeting a subset, auto-discount per item, min-order on subtotal, usage-limit counting.
- Payment: one Paystack init for order total, verify, **all** items → pending enrollments + Moodle jobs; idempotent webhook replay.
- Capacity: seat gone before payment.
- Regression: full existing suite stays green.

## 9. Phased delivery
| Phase | Deliverable | Est. |
|---|---|---|
| **A — Cart foundation** | `carts`/`cart_items`, `Cart`/`CartService`, add/remove, `/cart` page, nav badge, shared prop, guest→user merge | ~3–4 days |
| **B — Multi-item order + checkout** | `createFromCart`, cart checkout (customer step → review → one Paystack), enrollment fan-out verified | ~3–4 days |
| **C — Multi-item discounts** | per-item redemptions, order-level codes + min-order, usage-limit semantics | ~3–5 days |
| **D — Guards, capacity, polish, tests** | all edge cases, capacity checks, rate limits, full test suite | ~2–3 days |

## 10. Risks & mitigations
- **Discount engine multi-item semantics (highest risk)** → isolate in `CartDiscountService`, heavy tests, keep single-item path untouched.
- **Installment coupling** → sidestep by scoping installments to single-item (D1).
- **Capacity race** → best-effort check + re-check at provisioning + explicit sold-out handling; document the refund/hold policy decision.
- **Programs regression** → programs never touch cart; keep `create(Product,...)` intact; regression tests on the program funnel.
- **Scope creep (corporate, free, quantity)** → explicitly deferred (§3).

## 11. Decisions needed before build
D1 installments (single-item-only?), D2 buy-now + cart or cart-only, D3 guest cart storage, D4 discount scope, D5 price snapshot re-validation, and the **capacity/oversell policy** (block at checkout vs allow + reconcile). Recommendations are in §4; confirm to finalize the plan.
