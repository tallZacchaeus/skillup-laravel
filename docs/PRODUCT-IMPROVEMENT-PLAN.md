# SkillUp — Product Improvement Plan & Roadmap

**Goal:** evolve SkillUp from a feature-complete pre-launch platform into a world-class EdTech
marketplace on par with Udemy / Coursera — while shipping the two requested features
(Summer AI on the home page, and real filtering on the courses page) as the first slice.

**Author's lenses:** this plan is written wearing five hats — product manager, UI/UX,
software engineer, growth marketer, and security — so each recommendation names *why it matters*,
*what it costs*, and *how it fits the existing architecture*.

> **Status of the codebase:** Laravel 13 + Filament 3 (4 role panels) + Inertia/React 18 +
> Tailwind, on SQLite (dev) / Paystack / Moodle / Discourse. It's at **Phase 15 — deployment
> cutover** (feature-complete, pre-launch). This is polish-and-elevate work, not a rebuild.

---

## 1. Where we are today (grounded assessment)

### 1.1 Two parallel course systems
There are **two** systems that both represent "courses," and they are only loosely connected:

| System | Model chain | Front door | Payment |
|---|---|---|---|
| **Catalog** | `Track → Product → ProductPrice` (+ `CourseLevel`, `Cohort`) | `/courses` | `/checkout` → Paystack |
| **Programs** | `Program → ProgramEdition → ProgramEditionTrack → Product` | `/programs/{slug}` | Guardian funnel → **same** `/checkout` engine |

- **`Track` is effectively the category** (Software Development, Data Analysis, Product Design, Product Management, Virtual Assistance, Digital Marketing, Cybersecurity). `phase` (`launch` / `phase 2` / `programs`) is just a rollout stage.
- **Programs** (e.g. *Summer AI Bootcamp 2026*) are cohort/flagship experiences with a bespoke, mobile-first funnel: micro-form → email OTP verification → seat hold → pay-while-hot → post-payment safeguarding onboarding. They deliberately **reuse the catalog checkout** — no parallel payment path.

### 1.2 What "Summer AI" actually is (seeded data confirmed)
`Program: summer-ai` → `Edition: 2026` → **2 edition-tracks**, each backed by a real published `Product`:
- **Alpha AI** (ages 8–13) → `summer-ai-2026-alpha-ai`, ₦100,000
- **AI Explorer** (ages 14–18) → `summer-ai-2026-ai-explorer`, ₦100,000
- Both sit under an **internal catalog Track** `skillup-plus-programs` (`metadata.internal = true`), each with a 10% early-bird `DiscountRule`.

### 1.3 The concrete gaps blocking the two requests
1. **Home page shows nothing dynamic.** The `/` route loads only faqs/testimonials/partners/posts. The "Courses & Programs" section (`CoursesPrograms.jsx`) is **100% hardcoded** from `resources/js/data/site.js` (only "SkillUp Plus" and "Tech Trybe" — **no Summer AI**).
2. **The courses page has no real filtering.** `PublicCourseController::index` dumps *all* published products + tracks to React; the search box is decorative and the "Filters" button is dead. Category is derived from `track.phase`; there are no query params, no facets, no pagination, no sort.
3. **Hero search is broken end-to-end.** The home hero posts `?search=` to `/courses`, which ignores it.

### 1.4 Correctness / safety / security issues found (must-fix before launch)
> These surfaced during discovery and matter for a "world-class" bar. Ranked by severity.

| # | Severity | Issue | Where |
|---|---|---|---|
| S1 | **High (child safety)** | Summer AI child-program products **already appear in `/courses`** and can be bought through the generic checkout, **bypassing the guardian consent, age→track validation, seat-hold, and safeguarding onboarding** funnel. Their card also links to the raw catalog track, not `/programs/summer-ai`. | `PublicCourseController` doesn't filter `metadata.internal`; verified live on `/courses`. |
| S2 | **High** | **Paystack webhook empty-secret bypass** — if `PAYSTACK_WEBHOOK_SECRET` is unset, signature verification is skipped and forged payment events are accepted. Should hard-fail. | `PaystackWebhookController.php:20` |
| S3 | **High** | **Guest checkout creates no enrollment** — a paid order with no `user_id` yields a receipt but **no Enrollment and no Moodle access**. No account-creation/link step. | `PaymentService::createPendingEnrollments` |
| S4 | Medium | **No security headers / CSP** anywhere (no CSP, HSTS, X-Frame-Options, X-Content-Type-Options, Referrer-Policy) on a payment + PII site. | app/config-wide |
| S5 | Medium | **Public forms are un-throttled** (`/leads/*`, `/checkout/discount/validate`) — spam, discount-code brute-forcing, unbounded row + email creation. | `routes/web.php` |
| S6 | Low | Watermarked "Unsplash+" placeholder imagery on the homepage hero/backgrounds (licensing). | `public/images/*` |

### 1.5 Missing marketplace primitives (vs Udemy / Coursera)
- **No ratings & reviews** — no model, no aggregate score, no moderation. The single biggest missing trust/ranking signal.
- **No real search** — no Scout/Meilisearch/Typesense; catalog is a full client-side dump. No relevance, facets, typo-tolerance, or pagination.
- **No real taxonomy** — no Category/Topic/Tag models, no product↔tag pivot, no global normalized level (levels are per-track rows), `delivery_mode` is un-enumerated/un-indexed.
- **Weak curation/merchandising** — only a raw `is_featured` boolean + numeric `sort_order`; no collections, bundles, "trending / most-popular," or ranking controls.
- **No self-serve course-authoring** — courses are Filament forms with JSON `syllabus`; learning content lives in Moodle. (Fine for an instructor-led model — flagged, not necessarily a goal.)

---

## 2. The two requested features — detailed plan

I recommend shipping these in **two passes**: a fast **v1** that reuses existing fields (days),
then a **v2** that adds the real taxonomy foundation (following week).

### 2.1 Feature A — Summer AI + programs & featured courses on the home page

**v1 (fast, dynamic, safe):**
1. Update the `/` route to also pass:
   - `programs` → active `Program`s with their `currentEdition()` (title, dates, price from the edition's product default price, hero image, seats left, `/programs/{slug}` URL).
   - `featuredCourses` → `Product::published()->where('is_featured', true)` **excluding internal program products**, formatted like the courses page (reuse a shared `ProductPresenter`).
2. Replace the static `CoursesPrograms.jsx` with a data-driven version:
   - **Programs tab** → renders real programs (Summer AI Bootcamp 2026 card with real dates, "Registration open", seats, early-bird badge) linking to the **program funnel** `/programs/summer-ai`.
   - **Courses tab** → renders featured catalog courses linking to `/courses/{track}/{product}`.
   - Keep `data/site.js` only as an empty-state fallback.
3. Add a "Summer AI" highlight/banner slot (seasonal) on the home hero or just below it.

**Why:** surfaces the live flagship program with correct routing into the safeguarding funnel
(also resolves S1's routing half), and makes "featured" a real, admin-controllable merchandising lever.

### 2.2 Feature B — Real filtering on `/courses`

**v1 (server-side filtering with existing fields):**
Rework `PublicCourseController::index` to accept query params and filter in the DB:

| Filter | Implementation (v1) |
|---|---|
| **Search** | `where('title'/'subtitle'/'description' LIKE)` + track title; wire the hero + page search box to it. |
| **Program** | Join `program_edition_tracks.product_id` → filter products belonging to a chosen `Program`. Expose program name on each card. |
| **Category** | Filter by `Track` (the de-facto category) via `track_id`/slug. |
| **Level** | Filter by `CourseLevel.name` / `rank`. |
| **Delivery mode** | Filter by `products.delivery_mode` (normalize casing). |
| **Price** | Range filter on the active `defaultPrice.amount`; buckets (Free / <₦50k / ₦50–150k / ₦150k+) + "Free/Waitlist". |
| **Sort** | `sort_order`, newest (`published_at`), price asc/desc, (later: rating, popularity). |
| **Pagination** | Paginate (e.g. 12/page) instead of dumping everything. |

- **Exclude internal program products** from the generic grid (or badge them "Register via program" and link to `/programs/...`) → **fixes S1**.
- Build a proper **filter sidebar + active-filter chips + sort dropdown + working search**, using **Inertia partial reloads** (`router.reload({ only: [...] })`) so filtering feels instant and is shareable/bookmarkable via the URL.
- Add facet **counts** next to each option.

**v2 (real taxonomy foundation — the Udemy/Coursera upgrade):**
- New `categories` (a.k.a. topics) table + `product_category` pivot (many-to-many, `is_primary`), and a `product_tag` pivot for skills. Seed from existing tracks/tools.
- Canonical **level enum** (or `level_rank` column on products) for a reliable global Beginner/Intermediate/Advanced facet.
- Index `delivery_mode` and price; normalize `delivery_mode` to an enum.
- Filament UI so admins manage categories/tags and assign them to products.
- Optional: move to **Laravel Scout + Meilisearch/Typesense** for search relevance, typo-tolerance, and fast faceting as the catalog grows.

---

## 3. Roadmap to world-class (by theme, prioritized)

### 3.1 Discovery & catalog  ⭐ highest leverage
- Real taxonomy (categories/topics/tags) + faceted search (§2.2 v2).
- **Scout + Meilisearch/Typesense** search with autocomplete, synonyms, typo tolerance.
- Course detail upgrades: curriculum accordion, outcomes, requirements, instructor bio, FAQ, "what you'll build," preview.
- Curation surfaces: **featured collections**, **bundles**, "Most popular," "New," "Trending," personalized "Because you viewed…".

### 3.2 Social proof & trust  ⭐
- **Ratings & reviews** system: `reviews` model (rating 1–5, title, body, verified-purchase flag), aggregate score + count cached on Product/Track, Filament moderation, public display, "verified learner" badge. Gate submission to enrolled learners.
- Enrollment counts / "1,200+ learners," graduate outcomes, testimonials tied to specific courses.
- Public **instructor profiles** (bio, courses, rating) — `InstructorProfile` exists; make it public.

### 3.3 Learner experience
- Learner dashboard: enrolled courses, progress (sync from Moodle), certificates, upcoming cohort sessions, community link.
- **Wishlist / save-for-later**, and a **cart** (multi-item checkout).
- Learning paths / roadmaps (track → sequenced courses) with progress.
- In-app notifications (already modeled) surfaced well; email digests.

### 3.4 Conversion & commerce
- **Fix guest checkout (S3):** auto-create/link an account from `order.metadata.customer.email` on payment so enrollment + Moodle provisioning happen; magic-link to set password.
- **Free-course instant-enroll** path (zero-price fast path, no Paystack round-trip).
- Coupon UX at checkout (apply/preview discount), price strike-through using `compare_at_amount` (already in schema), multi-currency display, installment clarity.
- Abandoned-checkout recovery emails; seat-scarcity and cohort-countdown cues (honest urgency).
- Refund/chargeback webhook handling (currently logged, not acted upon).

### 3.5 Programs module polish
- First-class **bridge** between programs and catalog: program landing links to its courses; course cards for program products route into the funnel (§S1).
- Reusable program landing renderer (content-JSON section builder already designed) — ensure Summer AI's blocks all render.
- Waitlist + sibling-registration flows surfaced; seat-hold countdown UI.

### 3.6 Marketing & growth
- **SEO**: server-rendered meta per page, JSON-LD structured data (`Course`, `Organization`, `BreadcrumbList`, `AggregateRating`), XML sitemap, canonical URLs, per-course OG images.
- Referrals / ambassadors (route stubs exist), affiliate tracking, UTM capture (already partial).
- Lifecycle email (welcome, onboarding, re-engagement) via the existing notification stack; newsletter double opt-in.
- Blog/content engine SEO (already have Post/PostCategory) — topic clusters, related courses on posts.
- Analytics: product analytics (events), conversion funnels, dashboards.

### 3.7 Security & compliance  ⭐ (pre-launch blockers in bold)
- **Hard-fail Paystack webhook on missing secret (S2).**
- **Add a security-headers/CSP middleware (S4)** — CSP, HSTS, X-Frame-Options, X-Content-Type-Options, Referrer-Policy, Permissions-Policy.
- **Throttle public forms & discount validation (S5).**
- **Fix guest→enrollment gap (S3).**
- Consistent **FormRequest** validation classes; audit mass-assignment (already disciplined).
- 2FA for admin/instructor panels; audit-log coverage (model exists).
- Cookie-consent + privacy policy + data-export/delete (GDPR/NDPR) — partial export exists.
- Replace watermarked stock imagery (S6); asset licensing pass.

### 3.8 Performance, accessibility & ops
- Image pipeline (responsive `srcset`, WebP/AVIF, lazy) — hero is a large JPG today.
- Pagination + query caching for catalog; HTTP caching/CDN for public pages.
- Accessibility audit (WCAG AA): focus states, contrast (the wordmark's navy half is low-contrast on dark), keyboard nav, reduced-motion (partially done).
- Observability: error tracking (Sentry), uptime + smoke tests (smoke command exists), structured logging, queue/Horizon monitoring.

---

## 4. Suggested phased sequencing

| Phase | Theme | Highlights | Rough effort |
|---|---|---|---|
| **Phase 0 — the two asks + safety** | Ship requested features + fix S1/S2/S5 | Home programs+featured (dynamic), courses filters v1, exclude/route program products, hard-fail webhook secret, throttle forms | ~3–5 days |
| **Phase 1 — trust & discovery** | Reviews & real taxonomy/search | Ratings & reviews, categories/tags + faceted search (Scout/Meili), course-detail upgrade, guest→enrollment fix (S3), CSP headers (S4) | ~2–3 weeks |
| **Phase 2 — conversion & learner** | Cart/wishlist, dashboards | Wishlist+cart, free-course path, coupon UX, learner dashboard + progress, collections/bundles | ~2–3 weeks |
| **Phase 3 — growth & scale** | SEO, marketing, ops | JSON-LD/SEO, lifecycle email, referrals, analytics, perf/a11y, observability | ~2–3 weeks |

---

## 5. Recommended immediate next steps (Phase 0)

1. **Home page:** make `CoursesPrograms` data-driven; feature Summer AI (→ `/programs/summer-ai`) + featured catalog courses; wire hero search to real results.
2. **Courses page:** server-side filtering (search, program, category/track, level, delivery, price, sort) + pagination + a real filter UI; exclude internal program products from the generic grid (fixes the child-safety routing issue) or badge + route them to the program funnel.
3. **Safety quick-wins alongside:** hard-fail Paystack webhook on empty secret; add throttling to public/lead/discount routes.
4. **(Decision needed)** taxonomy depth for the "category" filter — reuse `Track` as category now (v1) vs. introduce a real `Category`/`Tag` model now (v2). *Recommendation: ship v1 with Track-as-category in Phase 0, add the real taxonomy in Phase 1.*

---

*This document is the plan only — no application code has been changed to implement it yet
(the earlier branding/favicon fixes are already live). Tell me which phase/items to start on.*
