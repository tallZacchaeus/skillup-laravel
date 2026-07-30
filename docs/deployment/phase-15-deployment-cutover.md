# Phase 15 Deployment And Cutover Runbook

This runbook prepares the Laravel platform for production launch while keeping the current WordPress/WooCommerce site available until cutover is explicitly approved.

## Preflight

- Confirm PHP 8.3+, Composer, Node 22 LTS, database, queue worker, scheduler, SSL, a **Meilisearch** instance, and the PHP `intl` extension are installed. Filament admin tables will fail without `intl`; the course catalogue search falls back to the database if Meilisearch is unreachable, but Meilisearch is required for production-grade relevance and facets.
- Copy `.env.production.example` to `.env` on the production server and fill only production secrets on the server.
- Run `php artisan key:generate --force` on production if `APP_KEY` is empty.
- Run `php artisan skillup:deployment-readiness --production` and fix every failed check.
- Run `npm ci && npm run build` during the release build.

## Production Deploy Commands

```bash
composer install --no-dev --prefer-dist --optimize-autoloader
npm ci
npm run build
php artisan down --render="errors::503" --retry=60
php artisan migrate --force
php artisan db:seed --class=RolesAndPermissionsSeeder --force
php artisan db:seed --class=NotificationSeeder --force
php artisan db:seed --class=FutureModuleSeeder --force
php artisan storage:link
php artisan scout:sync-index-settings
php artisan scout:import "App\Models\Catalog\Product"
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
php artisan up
php artisan skillup:deployment-readiness --production
php artisan skillup:smoke-test --url="${APP_URL}"
```

## Infrastructure

- Queue: install `deploy/supervisor/skillup-worker.conf.example`, update paths/user, then run `supervisorctl reread`, `supervisorctl update`, and `supervisorctl status`.
- Search (Meilisearch): install `deploy/supervisor/skillup-meilisearch.conf.example` (or run Meilisearch as a systemd/managed service), set a strong `MEILISEARCH_KEY` master key, bind it to localhost or a private network, and verify `curl "$MEILISEARCH_HOST/health"` returns `{"status":"available"}`. Re-run `php artisan scout:sync-index-settings` and `php artisan scout:import "App\Models\Catalog\Product"` after any Meilisearch reset. Product changes reindex automatically via Scout (`SCOUT_QUEUE=true` recommended so indexing runs on the queue worker).
- Scheduler: install `deploy/cron/skillup-scheduler.example` for the web user and verify `php artisan schedule:list`.
- Storage: run `php artisan storage:link`; confirm uploaded resource files and public media load through `/storage`.
- Backups: configure `BACKUP_DISK`, `BACKUP_RETENTION_DAYS`, database backups, uploaded file backups, and restore testing.
- Domain and SSL: point the Laravel domain to the new server only after smoke tests pass; use the Nginx example as a starting point.

## Credentials

- Paystack: production public key, secret key, webhook secret, and webhook URL `/webhooks/paystack`.
- Moodle: create active Moodle connections in Admin and run `php artisan skillup:moodle-import`.
- ZeptoMail: production API key and sender.
- SES: IAM credentials and verified sender/domain for fallback.
- WhatsApp: Cloud API phone number ID, token, and approved templates.
- Discourse: base URL, SSO secret, API key, API username, groups, and local mappings.
- Search: `SCOUT_DRIVER=meilisearch`, `MEILISEARCH_HOST`, and `MEILISEARCH_KEY` (master key). Keep `SCOUT_QUEUE=true` in production.

## SEO

- `/sitemap.xml` and `/robots.txt` are generated dynamically (no static files) and use `APP_URL`; confirm both return correctly once the domain is live.
- Submit `https://<domain>/sitemap.xml` to Google Search Console after cutover.
- Course pages emit `Course` + `AggregateRating` JSON-LD; validate a sample URL with Google's Rich Results Test.

## Cutover

- Keep WordPress/WooCommerce live until Laravel payment and Moodle provisioning pass production smoke tests.
- Freeze WooCommerce product, price, coupon, and checkout edits before final migration.
- Export any final WooCommerce orders/enrollments that need migration or reconciliation.
- Switch public enrollment/apply links from WordPress to Laravel course/checkout URLs.
- Disable WooCommerce checkout after Laravel checkout and Paystack webhooks are confirmed.
- Keep WordPress read-only or maintenance-visible until the rollback window closes.

## Post-Launch Monitoring

- Payments: Paystack initialization, callbacks, receipts, invoices, refunds.
- Webhooks: duplicate handling, failed webhook events, retries. `refund.processed` webhooks record a `PaymentRefund` and, on a full refund, cancel the order + suspend the learner's enrollments and Moodle access; `refund.failed`/`refund.pending` are recorded only. Chargeback disputes (`charge.dispute.*`) are still logged for manual handling.
- Moodle: pending, partial, failed, and active enrollments; admin retry action.
- Notifications: ZeptoMail sends, SES fallback, WhatsApp delivery logs, learner in-app notifications.
- Discourse: SSO login, group sync additions/removals, sync failures.
- Queues: worker status, failed jobs, queue depth, retry pressure.
- Search: Meilisearch health, index document count, and that `/courses` reports the Meilisearch engine (not the database fallback) under normal operation.
- Scheduler: installment reminders, Moodle imports, Moodle reconcile, failed job pruning.
- Support: tickets, contact leads, corporate inquiries, download leads.
- Public UX: homepage, catalogue, checkout, resources, events, mobile navigation.

## Rollback

- Do not delete or overwrite the existing WordPress/WooCommerce site during cutover.
- If payment, webhook, or Moodle enrollment fails in production, restore enrollment links to WordPress and keep Laravel in maintenance mode.
- Preserve Paystack webhook payloads, failed jobs, LMS logs, and notification logs for reconciliation before retrying cutover.
