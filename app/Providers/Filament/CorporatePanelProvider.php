<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Pages\Auth\Login;
use Filament\Pages\Auth\PasswordReset\RequestPasswordReset;
use Filament\Pages\Auth\PasswordReset\ResetPassword;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Illuminate\Contracts\View\View;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class CorporatePanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('corporate')
            ->path('corporate-portal')
            ->login()
            ->passwordReset()
            // Enterprise-blue branded left column on every corporate auth page
            // (self-scoped in CSS via :has(.skillup-auth-brand); hidden on mobile).
            ->renderHook(
                PanelsRenderHook::FOOTER,
                fn (): View => view('filament.auth.brand', [
                    'variant' => 'corporate',
                    'markSuffix' => 'Corporate',
                    'eyebrow' => 'Corporate learning portal',
                    'headline' => 'Develop your workforce.',
                    'lede' => 'Manage your team’s learning, track enrolments and progress, and keep your organisation’s development on course — all in one secure workspace.',
                    'bullets' => ['Manage team members and enrolments', 'Track workforce learning progress', 'Access organisation reporting'],
                    'secure' => 'Secure corporate learning portal. Only authorised organisation administrators may access this workspace.',
                ]),
                scopes: [Login::class, RequestPasswordReset::class, ResetPassword::class],
            )
            ->renderHook(
                PanelsRenderHook::AUTH_LOGIN_FORM_AFTER,
                fn (): View => view('filament.auth.support-note', $this->supportNote()),
            )
            ->renderHook(
                PanelsRenderHook::AUTH_PASSWORD_RESET_REQUEST_FORM_AFTER,
                fn (): View => view('filament.auth.support-note', $this->supportNote()),
            )
            ->colors([
                'primary' => Color::hex('#1E3A8A'),
                'gray' => Color::Slate,
            ])
            ->font('Jost')
            ->brandName('SkillUp Corporate')
            ->brandLogo(asset('images/skillUp.png'))
            ->brandLogoHeight('1.75rem')
            ->favicon(asset('favicon.ico'))
            ->viteTheme('resources/css/filament/skillup/theme.css')
            ->sidebarCollapsibleOnDesktop()
            ->discoverResources(in: app_path('Filament/Corporate/Resources'), for: 'App\\Filament\\Corporate\\Resources')
            ->discoverPages(in: app_path('Filament/Corporate/Pages'), for: 'App\\Filament\\Corporate\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Corporate/Widgets'), for: 'App\\Filament\\Corporate\\Widgets')
            ->widgets([])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }

    /**
     * @return array<string, string>
     */
    private function supportNote(): array
    {
        return [
            'variant' => 'corporate',
            'secure' => 'Secure corporate learning portal. Only authorised organisation administrators may access this workspace.',
            'supportUrl' => url('/contact'),
            'supportLabel' => 'Contact corporate support',
        ];
    }
}
