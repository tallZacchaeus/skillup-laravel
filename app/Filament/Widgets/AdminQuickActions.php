<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\CohortResource;
use App\Filament\Resources\DiscountCodeResource;
use App\Filament\Resources\EventResource;
use App\Filament\Resources\ExportRequestResource;
use App\Filament\Resources\ProductResource;
use App\Filament\Resources\ProgramResource;
use Filament\Widgets\Widget;

/**
 * Quick-action shortcuts for common admin tasks. Each card resolves a real
 * resource route (create/index); a card whose route or model policy is
 * unavailable is dropped, so nothing links to a page the admin can't open.
 */
class AdminQuickActions extends Widget
{
    protected static string $view = 'filament.widgets.admin-quick-actions';

    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = -9;

    protected function getViewData(): array
    {
        $candidates = [
            ['label' => 'Create course', 'icon' => 'heroicon-o-book-open', 'url' => static::create(ProductResource::class)],
            ['label' => 'Create cohort', 'icon' => 'heroicon-o-user-group', 'url' => static::create(CohortResource::class)],
            ['label' => 'Create programme', 'icon' => 'heroicon-o-academic-cap', 'url' => static::create(ProgramResource::class)],
            ['label' => 'Create discount', 'icon' => 'heroicon-o-ticket', 'url' => static::create(DiscountCodeResource::class)],
            ['label' => 'Create event', 'icon' => 'heroicon-o-calendar-days', 'url' => static::create(EventResource::class)],
            ['label' => 'Exports & reports', 'icon' => 'heroicon-o-document-arrow-down', 'url' => static::index(ExportRequestResource::class)],
        ];

        return ['actions' => array_values(array_filter($candidates, fn ($a) => $a['url'] !== null))];
    }

    private static function create(string $resource): ?string
    {
        return static::safe(fn () => $resource::getUrl('create'));
    }

    private static function index(string $resource): ?string
    {
        return static::safe(fn () => $resource::getUrl());
    }

    private static function safe(\Closure $fn): ?string
    {
        try {
            return $fn();
        } catch (\Throwable) {
            return null;
        }
    }
}
