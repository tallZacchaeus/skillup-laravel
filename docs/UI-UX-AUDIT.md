# SkillUp — UI/UX Audit & Recommendations

**Date:** 2026-07-29
**Rubric:** `ui-ux-pro-max` skill (10 priority categories: Accessibility, Touch & Interaction, Performance, Style, Layout/Responsive, Typography/Color, Animation, Forms/Feedback, Navigation, Charts) + WCAG 2.2 AA.
**Method:** an injected accessibility/UX harness run on live pages (alt text, control names, form labels, heading order, landmarks, meta) + a full source audit of every page/component against the rubric + screenshots captured during the build.

> **Tooling notes.** The **21st.dev "Magic" MCP** requested is *not connected* in this session (its tools never surfaced) — and Magic is a component **generator**, not an auditor, so it doesn't change the findings; it can be used later to generate improved components. The in-app **browser pane was unstable** (intermittent 0-px viewport, pre-hydration reads, blank screenshots), so *geometry-based* live checks (pixel overflow, live tap-target sizes) were verified against the **source** instead — which is the precise source of truth for those anyway.

**Scope:** all public/marketing pages (home, about, contact, community, corporate), the catalogue (courses index, track, course detail), programs (index, edition), content indexes (blog, resources, events), commerce (cart, cart checkout, single checkout, order review/status), learner (wishlist, dashboard), auth (login, register, password reset, verify email), and the Filament admin/learner/instructor/corporate panels.

---

## 0. Fixes applied (2026-07-29)

- ✅ **Skip-to-content link** added (`PublicLayout`, targets `#main`; verified — first Tab focuses it).
- ✅ **Tap targets → 44px** (nav cart/wishlist, `button.jsx` `icon` size, WishlistButton overlay, AddToCartButton compact, star-rating input).
- ✅ **Contrast** — all readable `text-slate-400` → `text-slate-500` (≥4.5:1); placeholders fixed too.
- ✅ **Auth pages re-branded** — `GuestLayout` on a soft-blue backdrop with a "← Back to SkillUp" link; `PrimaryButton` and all Breeze inputs/checkbox recoloured from gray/indigo to **brand blue**.
- ✅ **Course preview video** — replaced the bare iframe with a **poster + play-button facade** (no black-box void; loads the embed only on click — also a perf win).
- ↩️ **`/schools` "404"** — **not a bug.** Confirmed by `FutureModulesTest`: planned modules are intentionally hidden (404) until activated in the admin, and the routes aren't linked anywhere. Reverted; behaviour unchanged.
- ✅ **Horizontal overflow** — verified clean at 320–1280 (see M8).

- ✅ **Success toasts (M1)** — `flash {status,error}` shared via Inertia; global `Toaster` (aria-live polite, self-dismiss) in `PublicLayout`. Verified: "Added to your cart." toast on add.
- ✅ **On-page breadcrumbs (M6)** — reusable `Breadcrumbs` on course detail (Courses › Track › Course) and track pages.
- ✅ **Required-field markers (M2)** — red `*` + `aria-required` on cart-checkout (`Field`) and all Breeze auth labels (`InputLabel`).

- ✅ **Image performance pass (H4)** — all 23 local raster assets converted to **WebP** (`cwebp -q 82`); a new reusable `Img` component serves the WebP via `<picture>` with the original as fallback and defaults to **lazy loading + async decode**, with `eager` + `fetchpriority=high` on the LCP hero of each page. Every `<img>` across the public front end now routes through it. Biggest wins: `hero.jpg` 740K→336K, `champions.png` 636K→64K, `skill_up.png` 540K→56K, `pic.png` 508K→48K, `abj.png` 140K→36K. A `Phase14QualityTest` guard now **fails the build** if a referenced local image lacks a WebP sibling. Bonus: the guard surfaced a **broken blog fallback** (`/images/default-blog.jpg` never existed) — repointed to the existing `consistent.jpg` placeholder.

Full test suite: **153/153** (+1 WebP guard). Still open (deferred): dashboard→LMS deep link (M7 — needs a configured Moodle), watermarked stock imagery (M5 — needs licensed assets). Remote catalogue thumbnails (Unsplash) still load as-is per the "leave imagery alone" constraint, but now also lazy-load + async-decode.

## 1. Overall verdict

A **strong, coherent, largely accessible** front end with a real design system (brand blue `#0D4EFF`, Jost/Montserrat, consistent `max-w-7xl` layout, tasteful GSAP motion). The fundamentals that most apps get wrong are right here: **semantic landmarks on every page, a single `h1` with no heading-level skips, 100% alt-text coverage, labelled form controls, proper focus rings, and `prefers-reduced-motion` support.**

The gaps are concentrated in **accessibility polish (contrast, tap targets, skip link), image performance (CLS), and micro-feedback (toasts)** — nearly all quick, high-leverage fixes.

### Scorecard (by rubric category)

| # | Category | Score | Headline |
|---|----------|:----:|----------|
| 1 | Accessibility | ★★★½ | Excellent semantics/labels/focus; fix contrast, add skip link |
| 2 | Touch & Interaction | ★★★ | Icon tap targets run 32–40px (need ≥44) |
| 3 | Performance | ★★★★ | WebP + lazy/eager applied; heavy PNGs cut ~90% |
| 4 | Style & Consistency | ★★★★ | Strong brand system; token drift + auth mismatch |
| 5 | Layout & Responsive | ★★★★ | Mobile-first, `min-h-dvh`, consistent containers |
| 6 | Typography & Color | ★★★½ | Good scale/fonts; `slate-400` text fails AA |
| 7 | Animation | ★★★★½ | Reduced-motion respected, restrained, meaningful |
| 8 | Forms & Feedback | ★★★½ | Labels/errors/loading good; no toasts / required marks |
| 9 | Navigation | ★★★★ | Active states, sticky, mobile menu; no breadcrumbs |
| 10 | Charts & Data | n/a | No public charts; admin uses Filament |

**Composite: ~3.7 / 5 — launch-ready, with a clear punch-list of mostly-fast improvements.**

---

## 2. What's working well (keep it)

- **Semantic structure** — `header / nav / main / footer` landmarks on every page; one `h1` per page; no heading-level skips (verified on home & course detail).
- **Images** — 100% carry an `alt` attribute; decorative images correctly use `alt=""`; hero uses `fetchpriority="high"`.
- **Controls** — every button/link has an accessible name (0 unnamed of 65 on home, 52 on course detail); icon-only buttons carry `aria-label`.
- **Focus & states** — `buttonVariants` ships `focus-visible:ring-2 ring-offset-2` + `disabled:opacity-50 disabled:pointer-events-none`; 108 focus-style usages across the app.
- **Motion** — `@media (prefers-reduced-motion: reduce)` neutralises animations; GSAP hero/marquee are restrained and meaningful.
- **Forms** — visible labels, error-below-field (`Field` component), `sr-only` labels for the hero/newsletter search, `autocomplete` on auth, password show/hide (Filament), YouTube preview `<iframe>` has a `title`.
- **Discovery UX** — Meilisearch instant + typo-tolerant search, faceted filters with live counts, skill chips, star ratings and social proof on cards/detail.
- **Empty states** — cart, wishlist, dashboard, and "no courses match" all have friendly empty states with a next action.
- **Responsive** — mobile hamburger with `aria-expanded`, `min-h-svh` (not `100vh`) on the hero, systematic grid breakpoints, consistent container widths.

---

## 3. Findings & recommendations

### 🔴 High priority

**H1 · Touch targets below 44×44px** *(Touch & Interaction — CRITICAL)*
Icon buttons render under the 44px (Apple) / 48dp (Material) minimum: the `icon` button size and nav cart/wishlist buttons are **40px** (`h-10 w-10`); the wishlist heart, compact add-to-cart, and `sm` buttons are **36px** (`h-9`); some avatars/controls are **32px** (`h-8 w-8`). Star-rating inputs use 28px hit areas.
→ **Fix:** raise interactive icon buttons to `h-11 w-11` (44px), or keep the visual size and extend the hit area with padding. Set the `icon` button size to 44px. Give the star-rating buttons ≥44px targets.

**H2 · No "skip to content" link** *(Accessibility)*
Keyboard and screen-reader users must tab through the entire primary nav on every page.
→ **Fix:** add a visually-hidden-until-focused "Skip to main content" link as the first focusable element in `PublicLayout`, targeting the `<main>` region (`#main`).

**H3 · Low-contrast text** *(Accessibility — 4.5:1)*
`text-slate-400` (#94a3b8 ≈ **2.9:1** on white) is used ~13× and `text-slate-300` ~7× for readable meta/helper text (card meta, "enrolled" dates, hints, empty-state copy). These fail WCAG AA for normal text.
→ **Fix:** use `slate-500`/`slate-600` for any text meant to be read; reserve `slate-300/400` for **icons/decorative** only. Re-check the navy-hero secondary text (`text-blue-100/200`) — those pass, but audit alongside.

**H4 · Images ship without dimensions or responsive sources** *(Performance — CLS)*
**0 of 36** `<img>` declare `width`/`height` or `aspect-ratio`; only 17 are `loading="lazy"`; none use `srcset`/WebP. Cards reserve height via fixed classes, but content images (blog, about, program hero) can shift layout as they load.
→ **Fix:** add `width`/`height` (or `aspect-ratio`) to all images; `loading="lazy"` on everything below the fold; serve WebP/AVIF with `srcset`/`sizes`. (The homepage hero/section stock images are also watermarked "Unsplash+" — replace with licensed assets, see M5.)

### 🟠 Medium priority

**M1 · No success confirmation (toasts)** *(Forms & Feedback)*
Add-to-cart / wishlist / remove use `redirect()->back()->with('status', …)`, but no toast renders it — users infer success only from the nav badge/button-state flip.
→ **Fix:** add a lightweight toast component (auto-dismiss 3–5s, `aria-live="polite"`, doesn't steal focus) wired to the shared `flash`/`status` prop.

**M2 · Required fields aren't visually marked** *(Forms & Feedback)*
Auth and cart-checkout inputs use the `required` attribute but show no asterisk/"required" cue; phone is explicitly "(optional)" while required fields are unmarked.
→ **Fix:** add a consistent required indicator (asterisk with a legend, or "required" text) and `aria-required`.

**M3 · Design-token drift** *(Style & Consistency)*
Arbitrary hex values (`bg-[#F0F3FF]`, `bg-[#E6EDFF]`, `text-[#5B5B5B]`, `text-[#1E1E1E]`, `text-[#F4F4F4]`) appear in the home "bento"/newsletter instead of `skillup-*`/`slate` tokens, and raw Tailwind slate/blue mixes with `skillup-*` tokens generally.
→ **Fix:** map those to semantic tokens (extend `tailwind.config` if needed); prefer `skillup-*` / `slate-*` over raw hex so themes and dark mode stay consistent.

**M4 · Auth pages don't match the brand** *(Style & Consistency)*
Login/register/password screens use the Breeze `GuestLayout` (dark card, generic form styling) — a visible step down from the polished `PublicLayout` marketing pages. The wordmark's navy "Skill" is also low-contrast on that dark background.
→ **Fix:** restyle the guest/auth layout to the SkillUp system (brand navy panel + light logo, matching inputs/buttons), so the sign-up funnel feels first-party.

**M5 · Watermarked stock imagery** *(Style / content quality — licensing)*
Home hero and several section/background images carry visible "Unsplash+" watermarks.
→ **Fix:** replace with licensed/owned imagery before launch (flagged in earlier work; intentionally left for now).

**M6 · No on-page breadcrumbs on deep pages** *(Navigation)*
Course detail emits a `BreadcrumbList` in JSON-LD but shows no visual breadcrumb for a 3-level catalogue (Courses → Track → Course); only a single "Back to {track}" link.
→ **Fix:** add a visible breadcrumb trail on track/course/program detail pages.

**M7 · Dashboard "Continue learning" dead-ends** *(Navigation / IA)*
The learner dashboard's course CTA links to the public marketing course page, not the actual LMS (Moodle) where the learner studies.
→ **Fix:** for active enrollments, deep-link to the Moodle course (using the stored `moodle_course_id`) or a launch action.

**M8 · Horizontal overflow — VERIFIED CLEAN ✅** *(Layout)*
Re-tested with headless Playwright across **320 / 360 / 375 / 414 / 768 / 820 / 1024 / 1280** on all 15 public pages: **zero horizontal overflow anywhere.** The earlier harness flag was the in-app pane's `vw:0` bug, not the app. Responsive reflow is correct: nav→hamburger below 768, filter sidebar→"Filters" toggle below 1024, 2-column checkout/hero → stacked, card grids 1→2→3 columns. **No action needed.**

### 🟢 Low priority / polish

- **L1** Per-button loading state on add-to-cart / wishlist / buy-now (the global Inertia progress bar mitigates, but a local spinner reads clearer).
- **L2** Icon-only nav (cart/wishlist) has `aria-label` (fine) — a hover tooltip would aid discoverability.
- **L3** `text-xs` (12px) is used widely for chips/meta — keep ≥12px and ensure contrast (ties to H3).
- **L4** Cart discount code is applied at submit; a **live preview** on the cart-checkout page would reduce uncertainty (already noted in the cart Phase C scope).
- **L5** Public site vs Filament admin are two visual languages — acceptable, but a shared color/logo token pass keeps them recognisably one brand.

---

## 4. Prioritised punch-list (quick wins first)

1. **Add a skip link** (H2) — ~15 min, big a11y win.
2. **Bump icon tap targets to 44px** (H1) — small, systematic edit across `WishlistButton`, `AddToCartButton`, nav, `button.jsx` `icon` size.
3. **Swap readable `slate-400/300` text → `slate-500/600`** (H3) — find/replace + spot-check.
4. **Add `width`/`height` (or `aspect-ratio`) + `loading="lazy"` to all images** (H4) — reduces CLS immediately; `srcset`/WebP as a follow-up.
5. **Add a toast for cart/wishlist actions** (M1) + **required-field markers** (M2).
6. **Restyle the auth layout to brand** (M4) and **add breadcrumbs** (M6).
7. **Replace watermarked imagery** (M5) and **fix the dashboard LMS link** (M7).

None of these are structural — the information architecture, component system, and interaction model are sound. This is polish on a solid base.
