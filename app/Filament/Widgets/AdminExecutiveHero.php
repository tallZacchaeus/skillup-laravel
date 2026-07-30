<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

/**
 * Operations command-centre banner. Time-aware greeting plus a real security
 * context strip (current environment + signed-in role). Only truthful,
 * non-sensitive context is shown — no fabricated "last login" or audit data.
 */
class AdminExecutiveHero extends Widget
{
    protected static string $view = 'filament.widgets.admin-executive-hero';

    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = -10;

    protected function getViewData(): array
    {
        return [
            'greeting' => $this->greeting(),
            'name' => auth()->user()?->name,
            'environment' => app()->environment(),
            'isProduction' => app()->isProduction(),
            'roles' => auth()->user()?->getRoleNames()->join(', ') ?: null,
        ];
    }

    private function greeting(): string
    {
        $hour = (int) now()->format('G');

        return match (true) {
            $hour < 12 => 'Good morning',
            $hour < 18 => 'Good afternoon',
            default => 'Good evening',
        };
    }
}
