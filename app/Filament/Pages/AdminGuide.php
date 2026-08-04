<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class AdminGuide extends Page
{
    protected static ?string $navigationGroup = 'Help';

    protected static ?string $navigationIcon = 'heroicon-o-book-open';

    protected static ?string $navigationLabel = 'Admin Guide';

    protected static ?string $title = 'Admin Guide';

    protected static ?int $navigationSort = 100;

    protected static string $view = 'filament.pages.admin-guide';
}
