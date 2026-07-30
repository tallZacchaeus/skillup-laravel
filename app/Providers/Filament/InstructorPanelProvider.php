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

class InstructorPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('instructor')
            ->path('instructor')
            ->login()
            ->passwordReset()
            // Branded left column on every instructor auth page (self-scoped in
            // CSS via :has(.skillup-auth-brand); hidden on mobile).
            ->renderHook(
                PanelsRenderHook::FOOTER,
                fn (): View => view('filament.auth.brand', [
                    'variant' => 'instructor',
                    'markSuffix' => 'Instructor',
                    'eyebrow' => 'Instructor portal',
                    'headline' => 'Teach. Guide. Grow.',
                    'lede' => 'Your focused workspace for delivering great courses — manage your cohorts, support learners, and keep every session on track.',
                    'bullets' => ['Manage your assigned cohorts', 'Support the learners you teach', 'Stay on top of upcoming sessions'],
                    'secure' => 'Protected instructor portal. Only authorized instructors may access this system.',
                ]),
                scopes: [Login::class, RequestPasswordReset::class, ResetPassword::class],
            )
            // Security reassurance + support link beneath the login and
            // password-request forms.
            ->renderHook(
                PanelsRenderHook::AUTH_LOGIN_FORM_AFTER,
                fn (): View => view('filament.auth.support-note', $this->supportNote()),
            )
            ->renderHook(
                PanelsRenderHook::AUTH_PASSWORD_RESET_REQUEST_FORM_AFTER,
                fn (): View => view('filament.auth.support-note', $this->supportNote()),
            )
            ->colors([
                'primary' => Color::hex('#EA580C'),
                'gray' => Color::Slate,
            ])
            ->font('Jost')
            ->brandName('SkillUp Instructor')
            ->brandLogo(asset('images/skillUp.png'))
            ->brandLogoHeight('1.75rem')
            ->favicon(asset('favicon.ico'))
            ->viteTheme('resources/css/filament/skillup/theme.css')
            ->sidebarCollapsibleOnDesktop()
            ->discoverResources(in: app_path('Filament/Instructor/Resources'), for: 'App\\Filament\\Instructor\\Resources')
            ->discoverPages(in: app_path('Filament/Instructor/Pages'), for: 'App\\Filament\\Instructor\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Instructor/Widgets'), for: 'App\\Filament\\Instructor\\Widgets')
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
            'variant' => 'instructor',
            'secure' => 'Protected instructor portal. Only authorized instructors may access this system.',
            'supportUrl' => url('/contact'),
            'supportLabel' => 'Contact support',
        ];
    }
}
