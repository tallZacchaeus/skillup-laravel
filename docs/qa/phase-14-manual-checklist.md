# Phase 14 Manual QA Checklist

Run this checklist after migrations, seeders, queues, and the Vite build are current in the target environment.

## Public Experience

- [ ] Public pages render on mobile and desktop: home, about, contact, community, corporate, blog, resources, events, courses.
- [ ] Course catalogue filters work and only show published tracks/products.
- [ ] Product publish/hide works: hidden products are not visible on catalogue, detail, or checkout routes.
- [ ] Checkout is clear and calm on mobile and desktop, including review, processing, success, pending, and failed states.
- [ ] Reduced motion is respected: marquee and live badges do not animate when the OS asks for reduced motion.

## Commerce

- [ ] Promo code works for eligible public checkout.
- [ ] Email-list discount works for uploaded eligible email addresses and rejects ineligible emails.
- [ ] Installment checkout creates the expected deposit and installment schedule.
- [ ] Paystack test payment initializes with the correct amount in subunits and redirects to Paystack checkout.
- [ ] Paystack payment verification marks the order paid, creates a receipt, and creates a pending enrollment.
- [ ] Payment webhook is idempotent: replaying the same Paystack payload does not duplicate receipts, enrollments, or webhook rows.
- [ ] Failed Paystack initialization or verification queues learner failure notifications.

## LMS And Community

- [ ] Moodle enrollment succeeds for a paid order and stores Moodle user/course identifiers.
- [ ] Failed Moodle enrollment is retryable through queue retry behavior and the admin retry action.
- [ ] Partial Moodle enrollment is visible when group assignment fails after course enrollment succeeds.
- [ ] Discourse SSO works for verified learners and rejects invalid signatures or unverified emails.
- [ ] Discourse group sync adds and removes mapped groups when enrollment status changes.

## Notifications

- [ ] ZeptoMail sends transactional email.
- [ ] SES fallback is configured and used when ZeptoMail delivery fails.
- [ ] WhatsApp critical alert path is logged for OTP, security, payment failure, installment reminder, and Moodle provisioning failure.
- [ ] In-app learner notifications appear in the learner panel and can be marked as read.

## Admin And Roles

- [ ] Role boundaries are enforced for Admin, Learner, Corporate, and Instructor panels.
- [ ] Admin can reach product, discount, payment webhook, Moodle sync, notification, Discourse, support, content, and operations resources.
- [ ] Non-admin panel users cannot reach Admin-only resources directly by URL.
- [ ] Read-only operational resources, including lead and form submission inboxes, cannot be created, edited, or deleted from Filament.
