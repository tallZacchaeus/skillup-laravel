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

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->passwordReset()
            // Edit-profile page (name, email, password) in the user menu.
            ->profile()
            // Command palette: Cmd/Ctrl + K opens global search across the
            // resources that opt in via getGloballySearchableAttributes().
            ->globalSearchKeyBindings(['command+k', 'ctrl+k'])
            // Dark "secure console" branded column on every admin auth page
            // (self-scoped in CSS via :has(.skillup-auth-brand); hidden on mobile).
            ->renderHook(
                PanelsRenderHook::FOOTER,
                fn (): View => view('filament.auth.brand', [
                    'variant' => 'admin',
                    'markSuffix' => 'Admin',
                    'eyebrow' => 'Administrator console',
                    'headline' => 'Secure administration workspace.',
                    'lede' => 'The control centre for the SkillUp platform. Access is restricted to authorised administrators and every sign-in is protected.',
                    'bullets' => ['Manage users, courses, and content', 'Oversee orders and operations', 'Configure platform settings'],
                    'secure' => 'Restricted administrator access. Authorised personnel only.',
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
                'primary' => Color::hex('#0D4EFF'),
                'gray' => Color::Slate,
            ])
            ->font('Jost')
            ->brandName('SkillUp Admin')
            ->brandLogo(asset('images/skillUp.png'))
            ->brandLogoHeight('1.75rem')
            ->favicon(asset('favicon.ico'))
            ->viteTheme('resources/css/filament/skillup/theme.css')
            ->sidebarCollapsibleOnDesktop()
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
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
            'variant' => 'admin',
            'secure' => 'Secure administration workspace. Authorised personnel only.',
            'supportUrl' => url('/contact'),
            'supportLabel' => 'Contact platform support',
        ];
    }
}
