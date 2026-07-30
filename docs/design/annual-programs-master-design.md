# Annual Programs — Master Design

**Version:** 1.1 (expert gap review incorporated) · **Status:** Proposed
**First edition:** Summer AI Bootcamp 2026 (Alpha AI + AI Explorer)
**Source materials:** `Skillup_Summer ai _2026.docx`, launch flyer (QR → skillupglobal.tech)

---

## 1. The problem this solves permanently

Summer AI is an **annual program that changes shape every year** — different theme, tracks,
venue, pricing, capacity, and content. A one-off landing page would be rebuilt from scratch
every year and would lose history. This design introduces a **Programs module**: a permanent
umbrella entity with yearly **editions**, where each edition is fully admin-configurable
(content, tracks, fields, pricing) without code changes. Next year, ops duplicates the 2026
edition, edits dates/theme/content in Filament, and publishes.

The same module also powers any future recurring program (December Coding Camp, Girls-in-Tech
Week, etc.) — not just Summer AI. It also **replaces the `/schools` future-module placeholder**
(`school_youth_program`), which this is the realization of.

## 2. What the 2026 edition needs (from the programme document)

| Fact | Value |
|---|---|
| Dates | Aug 3 – 28, 2026 (4 weeks), Mon–Fri 9:30 AM – 2:30 PM |
| Venue | Expression Nation Hub, Redemption City, Mowe, Ogun State (in-person) |
| Tracks | Alpha AI (ages 8–13) · AI Explorer (ages 14–18) |
| Capacity | 100 seats total, 50 per track |
| Fee | ₦100,000 per participant, all-inclusive |
| Early bird | 10% off (flyer) |
| Registration flow | Parent registers ward → team follows up on WhatsApp → track confirmation → payment |
| Completion | Certificate of Participation, Showcase Day (week 4) |

**Critical modeling insight:** the registrant is a **parent/guardian**; the participant is a
**minor ward**. The platform's current enrollment model assumes user = learner. Registrations
therefore need guardian + participant data, age validation against the track, and consent
capture (guardian consent, photo/media consent).

## 3. Data model

```
programs                     ← permanent ("Summer AI", slug: summer-ai)
└── program_editions         ← yearly (2026, theme, dates, venue, status, content JSON)
    ├── program_edition_tracks   ← Alpha AI / AI Explorer (age range, capacity, product_id)
    └── program_registrations    ← guardian + ward + status pipeline + order link
```

### programs
- `slug` (stable URL key), `name`, `tagline`, `description`, `is_active`, `sort_order`

### program_editions
- `program_id`, `year`, `title` ("Summer AI Bootcamp 2026"), `slug` (`2026`)
- `status`: `draft → announced → registration_open → sold_out → running → completed → archived`
- `starts_on`, `ends_on`, `schedule_text` ("Mon–Fri, 9:30 AM – 2:30 PM")
- `venue_name`, `venue_address`, `venue_map_url`, `delivery_mode` (in_person/online/hybrid)
- `capacity_total`
- `payment_mode`: `immediate` (pay at registration) or `after_confirmation`
  (2026 doc flow: team confirms track on WhatsApp first, then sends payment link)
- **`age_reference_date`** — the date ages are computed against (defaults to `starts_on`).
  Rule: *a ward's track is decided by their age on the program start date*, shown on the form.
- **`seat_hold_minutes`** (default 45) — how long a checkout-initiated seat hold lasts (§4).
- **`allow_installments`** (boolean, default false for 2026) — if enabled, the seat is taken at
  **deposit paid**, not full payment; balance chased via existing installment machinery.
- **`terms_url` / `refund_policy`** (rich text) — displayed and accepted at checkout.
- `content` (JSON) — **ordered section builder** (see §5); this is what absorbs yearly change
- `registration_fields` (JSON) — per-edition custom fields, each with
  `{key, label, type, options?, required}`. **Profile completeness = every `required` field
  answered** — this is what the certificate gate checks, so `required` is not optional sugar.
- `contact_whatsapp`, `contact_email`, `hero_media_id`, SEO fields, `metadata`

### program_edition_tracks
- `edition_id`, `name`, `slug`, `age_min`, `age_max`, `capacity`, `summary`,
  `curriculum` (JSON — the "4-week journey" table), `facilitator_note`, `sort_order`
- **`product_id`** → auto-provisioned Product per track. This is the key reuse decision:
  pricing, Paystack checkout, discount rules, receipts, refunds, and reporting all come free
  from the existing commerce stack. No parallel payment path.

### program_registrations
- `edition_id`, `track_id`, `uuid`
- Guardian: `user_id` (nullable — guest-friendly), `guardian_name`, `guardian_email`,
  `guardian_phone`, `guardian_whatsapp`
- Participant: `participant_name`, `participant_dob` (age computed and validated against the
  track's range; track auto-suggested from DOB), `participant_gender` (optional)
- Funnel state: `status` —
  `started → email_verified → payment_pending → paid → profile_completed → enrolled → completed`
  plus `waitlisted`, `cancelled`, `abandoned`
- Email verification: `email_verification_token` + `email_verification_otp` (6-digit, hashed),
  both expiring (~30 min), `email_verified_at`, `email_invalid_at` (set by bounce webhook)
- Seat hold: `seat_held_until` (set when checkout is initiated; expired holds release the seat)
- Resumable onboarding: `resume_token` (signed magic-link, expiring + rotated after profile
  completion), `profile_completed_at`
- `custom_fields` (JSON answers matching the edition's `registration_fields` — filled AFTER payment)
- **Safeguarding fields** (post-payment form, minors at a daily physical camp):
  `emergency_contact_name/phone`, `medical_notes` (allergies/conditions — **encrypted cast**),
  `first_aid_consent`, `authorized_pickups` (JSON list of name + phone — **encrypted cast**)
- `order_id` (nullable FK — set at payment), `sibling_group_uuid` (groups one guardian's wards),
  `source` (`qr`, `web`, `referral`, `walk_in`), `utm` (JSON captured on entry)
- `guardian_consent_at`, `media_consent` (boolean), `metadata`, timestamps
- **Dedupe rule:** unique on (`edition_id`, `guardian_email`, `participant_name`,
  `participant_dob`) — a repeat submission resumes the existing registration instead of
  creating a ghost.

### certificates (upgrades the `certificate_builder_verification` future module)
- `uuid`, `serial` (public verify code), `registration_id` / polymorphic subject,
  `recipient_name`, `program_title`, `issued_on`, `pdf_path`
- `/certificates/verify?serial=…` becomes a real page.

## 4. Flows

### Public registration — payment-first funnel (mobile-first — this is QR-code traffic)

Design principle: **collect the minimum, verify the email, take payment while intent is hot —
the rigorous form comes after the money.** People abandon long forms; they rarely abandon
something they've already paid for.

```
Step 1          Step 2              Step 3         Step 4 (post-payment, resumable)
Micro-form  →   Email confirm   →   Pay (₦100k) →  Full onboarding form → certificate eligible
~5 fields       Resend link/OTP     Paystack       consents, T-shirt, custom fields
status:started  email_verified      paid           profile_completed
```

1. `/summer-ai` → 301 → `/programs/summer-ai` (short link printed on flyers/QR, stable forever).
   Past editions stay addressable at `/programs/summer-ai/2026`.
2. **Step 1 — micro-form (≤5 fields):** guardian name, email, WhatsApp number, ward's first
   name + date of birth. Age is computed **as of `age_reference_date`** (program start) and
   auto-selects the track (and therefore the price/product); the rule is stated on the form.
   Creates the registration (`started`) + a `Lead`; ops notified via existing
   `NewLeadNotification`. UTM/`?src=` parameters captured. A repeat submission for the same
   guardian + ward **resumes** the existing registration (dedupe rule, §3) instead of forking.
   A concise **privacy notice** (children's data, NDPA) is linked here.
3. **Step 2 — email confirmation (Resend):** the email carries BOTH a verification link and a
   **6-digit OTP** the guardian can type without leaving the page — mobile QR users often
   can't or won't app-switch. The page shows an instant **"Resend code"** button (rate-limited)
   and check-your-spam guidance. Either path marks `email_verified` and lands the guardian
   **directly on the payment step**. Verification endpoints and OTP attempts are rate-limited
   (brute-force protection).
4. **Step 3 — payment:**
   - Initiating checkout places a **seat hold** (`seat_held_until = now + seat_hold_minutes`).
     Holds count toward capacity, so the last seat can never be sold twice; expired holds
     free the seat automatically. If the track fills mid-funnel, the guardian is offered the
     waitlist instead of a dead checkout.
   - Existing `/checkout/{product}` for the ward's track, registration UUID in order metadata;
     early bird auto-applied. **Early-bird boundary rule: the discount is honored if the order
     was created inside the window**, even if payment lands shortly after.
   - **Terms + refund policy** are displayed and accepted (checkbox) before payment.
   - **Offline payments are first-class:** many parents pay by bank transfer. Paystack's
     transfer channel is enabled, and admins additionally get a **"Record offline payment"**
     action (amount, proof upload, reference) that creates the order/receipt and marks the
     registration `paid` — no WhatsApp-screenshot bookkeeping outside the system.
   - Installments: only if the edition's `allow_installments` is on; the seat is then taken at
     deposit. Off for 2026 unless decided otherwise.
   - Paystack webhook flips the registration to `paid` and converts the hold into a taken
     seat. `payment_mode: after_confirmation` remains available per edition, but pay-first is
     the default.
5. **Step 4 — full onboarding form (post-payment, resumable):** consents (guardian + photo +
   first-aid), **emergency contact, medical/allergy notes, authorized pickup persons**,
   T-shirt size, and the edition's custom fields. Reached from the payment success page and
   from a **magic resume link** (`resume_token`, expiring, rotated once the profile completes)
   included in every email. Completing all `required` fields sets `profile_completed_at`.
   The success page also offers **"Register another child"** — guardian data and verification
   carry over (`sibling_group_uuid`), only the new ward's details and payment are needed.
6. **Certificate gate:** Certificates of Participation are only issuable when the registration
   is `completed` **and** `profile_completed_at` is set. The admin list shows a
   "profile incomplete" badge so ops can chase stragglers before Showcase Day.
7. Drip nudges (queued, via Resend/WhatsApp templates): unverified after 1h → resend link/OTP;
   verified-but-unpaid after 24h → payment reminder with seats-left urgency; paid-but-
   incomplete-profile → weekly reminder until complete (and a final one before week 4).
   Hard-bounced emails (§7) automatically fall back to the WhatsApp channel.
8. Early bird: a **DiscountRule** (percentage 10, `ends_at` = deadline, product-scoped,
   `requires_code = false`, `is_public`) — the existing discount engine applies and displays it;
   the landing page shows a countdown chip while it's active.
9. Capacity: `seats_taken = paid/profile_completed/enrolled/completed registrations + active
   seat holds` per track; when `>= capacity` the micro-form flips that track to **waitlist**
   mode automatically; edition flips to `sold_out` when all tracks are full.
10. **Refunds & seat release:** a refund (existing `payment_refunds`) or cancellation releases
    the seat and **auto-notifies the first waitlisted registration** with a time-boxed payment
    link. The refund policy shown at checkout is the contract for this.

### Completion
- **"Completed the program" is defined explicitly:** Phase 1 uses a manual per-registration
  "completed" toggle (ops judgment, matching the doc's "completes the four weeks"); Phase 2
  adds a simple daily attendance register so completion can be attendance-derived.
- Bulk action "Mark completed" → issues Certificates of Participation (PDF + verify serial)
  **only for registrations with `profile_completed_at` set**, emails guardians, prompts a
  testimonial (feeds the existing testimonials section).
- Showcase Day can be published through the existing **Events** module and cross-linked.

### Next year (the "permanent" part)
- Filament action **"Duplicate edition"** → copies tracks/content/fields with cleared dates and
  `draft` status. Ops edits theme/dates/fee, publishes. The `/summer-ai` QR from old flyers
  still lands on the newest open edition; 2026 becomes an archive page with photos and
  testimonials — social proof compounding year over year.

## 5. Landing page — master design (content section builder)

Each edition's `content` JSON is an **ordered array of typed sections**, edited in Filament with
a `Builder` field. The React side has one renderer component per block type. Yearly redesign =
reordering/adding blocks, no code.

Block types (v1, derived from the 2026 doc):

| Block | Content |
|---|---|
| `hero` | Badge ("For ages 8–18"), title, subtitle, CTA, media, early-bird chip |
| `quick_facts` | Dates / venue / hours / fee / capacity strip |
| `overview` | Rich text ("project-based, they build something real") |
| `why` | Icon bullet grid (real projects, rested facilitators, safe local hub, …) |
| `tracks` | Auto-rendered from `program_edition_tracks` (age badge, summary, seats left) |
| `journey` | Week-by-week timeline (Week 1 Foundations → Week 4 Showcase) |
| `includes` | "What's included" checklist (materials, internet, refreshments, T-shirt, certificate) |
| `team` | Roles table (supervisor, assistants, rotating facilitators) |
| `gallery` | Photos (mostly for archive editions) |
| `faqs` | Accordion (reuses `FaqAccordion`) |
| `venue` | Address + map embed + landmark note |
| `cta` | Closing register band with seat counter |

**Visual language** — extends the new public design system, no new one:
- Jost/Montserrat, skillup blue `#0D4EFF` / navy `#14183E`, orange `#F97316` accents matching
  the flyer's age-badge energy; bento cards like "What Sets Us Apart".
- GSAP: hero intro timeline, `data-reveal` scroll sections, **seat-counter count-up**
  (`data-count`), journey timeline progressive reveal, marquee for past-edition photos.
- Mobile-first with a **sticky bottom "Register — ₦100,000" CTA** on small screens (QR traffic
  arrives on phones), reduced-motion respected throughout.

## 6. Admin (Filament)

- **Programs** resource (Admin panel, "Programs" nav group): editions relation manager.
- **Edition** form: details tab · content tab (Builder) · tracks tab (repeater; product
  auto-created/synced on save) · registration fields tab (repeater) · settings tab.
- **Registrations** resource: pipeline tabs by status, filters by track/source, inline status
  actions, `wa.me/{guardian_whatsapp}?text=…` quick action using a per-edition WhatsApp
  template, CSV export via existing `ExportRequest`, duplicate detection on guardian phone.
- **Dashboard widgets** (existing widget patterns): seats filled per track (progress),
  registrations funnel this edition, edition revenue (via linked products), daily
  registrations sparkline during the open window.
- Notifications: reuse `notification_events` + WhatsApp/email template tables for
  "registration received", "track confirmed + payment link", "payment received",
  "programme starts Monday" reminders.

## 7. Email provider — Resend, platform-wide

Decision: **Resend becomes the email provider for the entire project**, replacing the current
ZeptoMail-primary / SES-fallback pair.

- Install `resend/resend-laravel`; `MAIL_MAILER=resend`, `RESEND_API_KEY` in env.
- The existing `EmailDeliveryService` abstraction stays — only the transport changes, so
  templates, delivery logs, and notification events are untouched. SES can remain as the
  fallback transport or be retired once Resend is proven.
- All program-funnel mail (verification link/OTP, payment receipt hand-off, resume link, drip
  nudges) rides this transport from day one.
- **Bounce/complaint handling:** wire Resend's webhooks — a hard bounce sets
  `email_invalid_at` on the registration, surfaces an "email invalid" badge in admin, and
  switches that registration's nudges to WhatsApp. Complaints suppress future sends.
- **Domain decision required now:** the flyer says `skillupglobal.tech`, the programme doc says
  `hello@skillupplus.ng` — pick ONE sending/landing domain before launch (the QR is already
  printed). Recommended: send from a subdomain (`mail.skillupglobal.tech`) with SPF, DKIM,
  **and DMARC** published; the from-address must be a real monitored mailbox.

## 8. Safeguarding, privacy & compliance (minors)

This program processes **children's personal data** under the Nigeria Data Protection Act
(NDPA 2023). The design commits to:

- **Guardian consent** captured with timestamp before any ward data is stored beyond the
  micro-form; separate, optional **photo/media consent** (Showcase Day photos are not implied).
- **Privacy notice** linked at the micro-form and onboarding form: what is collected, why,
  retention period, and how to request erasure.
- **Encryption at rest** (Laravel encrypted casts) for `medical_notes` and
  `authorized_pickups`; these fields are excluded from CSV exports by default and visible
  only to Admin/Super Admin roles.
- **Retention policy:** safeguarding data (medical, pickups) is purged N months after the
  edition completes (edition setting, default 6); the rest is retained for alumni history.
- **Daily operations:** the authorized-pickups list is the dismissal checklist; a printable
  per-track register (name, guardian phone, pickups, medical flags) is generated for the
  supervisor — access-logged.
- **Ops lead times flagged:** WhatsApp message templates require Meta approval (days, not
  hours) — submit templates well before launch week.

## 9. What is deliberately reused (no new infrastructure)

| Need | Existing machinery |
|---|---|
| Payment & receipts | Products + `/checkout/{product:slug}` + Paystack webhook |
| Early bird 10% | `discount_rules` (windowed, product-scoped, auto-apply) |
| Follow-ups | WhatsApp messages/templates + email templates + notification events (transport → Resend) |
| Lead capture | `leads` + `NewLeadNotification` |
| Exports | `export_requests` |
| Showcase Day | Events module |
| Social proof | Testimonials + partners + media assets |
| Placeholder replacement | `/schools` future module → real Programs index |

## 10. Build phases

**Phase 1 — ship 2026 registration** (items marked ⛔ were gap-review blockers and are
non-negotiable for launch)

1. Resend transport swap (platform-wide) + verification/resume mail templates,
   ⛔ bounce/complaint webhooks, DMARC/subdomain setup.
2. Schema + models (programs, editions, tracks, registrations, certificates table stub),
   ⛔ age-as-of-start-date rule, dedupe constraint, encrypted safeguarding fields.
3. Payment-first funnel: micro-form (privacy notice, UTM capture, resume-not-fork) →
   ⛔ email verification with OTP alternative + instant rate-limited resend →
   ⛔ seat holds at checkout initiation → terms/refund acceptance → checkout hand-off →
   post-payment onboarding form (⛔ emergency contact, medical, authorized pickups,
   first-aid consent) with expiring resume link and "Register another child".
4. ⛔ Offline payment recording (bank transfer proof → order/receipt → `paid`).
5. Landing renderer (section builder) with 2026 content seeded from the programme doc;
   `/summer-ai` short link; capacity/waitlist with holds; early-bird discount rule
   (honored-at-order-creation boundary).
6. Filament: Programs/Editions/Registrations resources with funnel-status tabs,
   "profile incomplete" and "email invalid" badges, WhatsApp quick action, restricted
   exports; edition dashboard widgets (seats + holds, funnel conversion by step and source,
   revenue, daily registrations).
7. Drip nudges (unverified 1h, unpaid 24h with seats-left urgency, incomplete-profile weekly,
   WhatsApp fallback on bounce). Submit WhatsApp templates to Meta early.

**Phase 2 — lifecycle**
- Certificates + `/certificates/verify` (gated on `completed` + `profile_completed_at`).
- Daily attendance register → attendance-derived completion.
- Refund → seat release → waitlist auto-promotion with time-boxed payment link.
- Combined sibling checkout (one payment, multiple wards) + optional sibling discount rule.
- Completed-edition archive view (gallery + testimonials), duplicate-edition action,
  Showcase Day event integration, safeguarding-data retention purge job.

**Out of scope here:** the doc's WhatsApp number and email are placeholders — real values are
edition settings, not code. The flyer/landing **domain decision** (`skillupglobal.tech` vs
`skillupplus.ng`) and DNS/APP_URL setup are deployment concerns — but must be decided before
launch because the QR code is already printed.
