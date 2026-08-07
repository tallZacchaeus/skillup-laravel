<?php

namespace App\Http\Middleware;

use App\Models\Platform\PageVisit;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Records one row per public page view for the admin "Visits & Users" page.
 *
 * Deliberately narrow. A visit is counted when a successful GET either renders
 * a full HTML document or is an Inertia navigation (the SPA sends X-Inertia on
 * client-side page changes — those are real page views, and the two cases never
 * overlap, so nothing is double counted).
 *
 * The write is deferred to the terminating phase so visitors never wait on it,
 * and any failure is swallowed: analytics must not be able to break the site.
 */
class RecordPageVisit
{
    /** Path prefixes that are never public page views. */
    private const IGNORED_PREFIXES = [
        'admin', 'learner', 'instructor', 'corporate-portal',
        'livewire', 'webhooks', 'api', 'storage', 'build',
        'healthz', 'readyz', 'up', 'sitemap.xml', 'robots.txt', 'favicon.ico',
    ];

    private const BOT_SIGNATURES = [
        'bot', 'crawl', 'spider', 'slurp', 'curl', 'wget', 'headless',
        'monitor', 'preview', 'fetch', 'python-requests', 'http-client',
        'lighthouse', 'pingdom', 'uptime',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($this->shouldRecord($request, $response)) {
            $attributes = $this->attributes($request);

            app()->terminating(function () use ($attributes): void {
                try {
                    PageVisit::create($attributes);
                } catch (\Throwable $e) {
                    Log::warning('Failed to record page visit', ['error' => $e->getMessage()]);
                }
            });
        }

        return $response;
    }

    private function shouldRecord(Request $request, Response $response): bool
    {
        if (! $request->isMethod('GET') || ! $response->isSuccessful()) {
            return false;
        }

        if ($request->is(...self::IGNORED_PREFIXES) || $request->is(...array_map(fn ($p) => $p.'/*', self::IGNORED_PREFIXES))) {
            return false;
        }

        if ($this->isBot((string) $request->userAgent())) {
            return false;
        }

        $isInertiaNavigation = $request->header('X-Inertia') === 'true';
        $isDocumentRequest = ! $request->ajax() && ! $request->pjax() && $request->acceptsHtml();

        return $isInertiaNavigation || $isDocumentRequest;
    }

    private function isBot(string $userAgent): bool
    {
        if ($userAgent === '') {
            return true;
        }

        $userAgent = strtolower($userAgent);

        foreach (self::BOT_SIGNATURES as $signature) {
            if (str_contains($userAgent, $signature)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array<string, mixed>
     */
    private function attributes(Request $request): array
    {
        $path = '/'.ltrim($request->path(), '/');

        return [
            'user_id' => $request->user()?->id,
            'visitor_id' => $this->visitorId($request),
            'path' => mb_substr($path, 0, 255),
            'referrer_host' => $this->referrerHost($request),
            'ip_address' => $request->ip(),
            'user_agent' => mb_substr((string) $request->userAgent(), 0, 1000),
            'visited_at' => now(),
        ];
    }

    /**
     * Stable per-browser identifier for unique-visitor counts. Falls back to the
     * IP + user agent pair when no session has been started yet.
     */
    private function visitorId(Request $request): string
    {
        $seed = $request->hasSession()
            ? $request->session()->getId()
            : $request->ip().'|'.$request->userAgent();

        return hash('sha256', $seed);
    }

    private function referrerHost(Request $request): ?string
    {
        $referrer = $request->headers->get('referer');

        if (! $referrer) {
            return null;
        }

        $host = parse_url($referrer, PHP_URL_HOST);

        // Internal navigation is not a traffic source worth reporting.
        if (! is_string($host) || $host === $request->getHost()) {
            return null;
        }

        return mb_substr($host, 0, 255);
    }
}
