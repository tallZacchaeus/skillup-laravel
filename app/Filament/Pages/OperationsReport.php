<?php

namespace App\Filament\Pages;

use App\Services\Operations\OperationalReportService;
use Filament\Pages\Page;

class OperationsReport extends Page
{
    protected static ?string $navigationGroup = 'Operations';

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar-square';

    protected static ?string $navigationLabel = 'Reports';

    protected static ?int $navigationSort = 10;

    protected static string $view = 'filament.pages.operations-report';

    /**
     * @var array<string, mixed>
     */
    public array $report = [];

    public function mount(OperationalReportService $reports): void
    {
        $this->report = $reports->summary();
    }

    public function refreshReport(): void
    {
        $this->report = app(OperationalReportService::class)->summary();
    }
}
