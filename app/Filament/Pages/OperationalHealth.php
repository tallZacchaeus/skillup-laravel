<?php

namespace App\Filament\Pages;

use App\Services\Operations\OperationalHealthService;
use Filament\Pages\Page;

class OperationalHealth extends Page
{
    protected static ?string $navigationGroup = 'Operations';

    protected static ?string $navigationIcon = 'heroicon-o-heart';

    protected static ?string $navigationLabel = 'Operational Health';

    protected static ?int $navigationSort = 20;

    protected static string $view = 'filament.pages.operational-health';

    /**
     * @var array<string, array<string, mixed>>
     */
    public array $checks = [];

    public function mount(OperationalHealthService $health): void
    {
        $this->checks = $health->snapshot();
    }

    public function refreshHealth(): void
    {
        $this->checks = app(OperationalHealthService::class)->snapshot();
    }
}
