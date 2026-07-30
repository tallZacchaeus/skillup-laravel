<?php

namespace App\Filament\Instructor\Pages;

use Filament\Pages\Page;

class InstructorEscalations extends Page
{
    protected static ?string $navigationGroup = 'Teaching';

    protected static ?string $navigationIcon = 'heroicon-o-exclamation-triangle';

    protected static ?string $navigationLabel = 'Escalations';

    protected static ?int $navigationSort = 40;

    protected static string $view = 'filament.instructor.pages.escalations';
}
