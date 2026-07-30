<?php

namespace App\Filament\Corporate\Widgets;

use App\Filament\Corporate\Pages\CorporateReports;
use App\Filament\Corporate\Pages\CorporateSupport;
use App\Filament\Corporate\Resources\CorporateEnrollmentResource;
use App\Filament\Corporate\Resources\CorporateOrderResource;
use App\Filament\Corporate\Resources\CorporateTeamMemberResource;
use Filament\Widgets\Widget;

/**
 * Quick-action shortcuts for the corporate portal. Every card links to a real
 * page/resource — nothing is shown for a feature without backend support.
 */
class CorporateQuickActions extends Widget
{
    protected static string $view = 'filament.corporate.widgets.quick-actions';

    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = -1;

    protected function getViewData(): array
    {
        return [
            'actions' => array_values(array_filter([
                ['label' => 'Team members', 'description' => 'Invite and manage your learners.', 'icon' => 'heroicon-o-user-group', 'url' => CorporateTeamMemberResource::getUrl()],
                ['label' => 'Enrollments', 'description' => 'Track course enrollment status.', 'icon' => 'heroicon-o-academic-cap', 'url' => CorporateEnrollmentResource::getUrl()],
                ['label' => 'Billing & orders', 'description' => 'Review invoices and balances.', 'icon' => 'heroicon-o-banknotes', 'url' => CorporateOrderResource::getUrl()],
                ['label' => 'Reports', 'description' => 'Export learning reports.', 'icon' => 'heroicon-o-document-chart-bar', 'url' => static::safeUrl(CorporateReports::class)],
                ['label' => 'Support', 'description' => 'Raise and track requests.', 'icon' => 'heroicon-o-lifebuoy', 'url' => static::safeUrl(CorporateSupport::class)],
            ], fn ($action) => $action['url'] !== null)),
        ];
    }

    /** Returns a page URL, or null if the page isn't registered — so the card hides. */
    private static function safeUrl(string $page): ?string
    {
        try {
            return $page::getUrl();
        } catch (\Throwable) {
            return null;
        }
    }
}
