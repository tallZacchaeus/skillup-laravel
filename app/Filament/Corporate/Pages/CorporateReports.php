<?php

namespace App\Filament\Corporate\Pages;

use Filament\Pages\Page;

class CorporateReports extends Page
{
    protected static ?string $navigationGroup = 'Corporate';

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar-square';

    protected static ?string $navigationLabel = 'Reports';

    protected static ?int $navigationSort = 40;

    protected static string $view = 'filament.corporate.pages.reports';
}
