<?php

namespace App\Filament\Learner\Pages;

use App\Models\Discourse\DiscourseConnection;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class CommunityLaunch extends Page
{
    protected static ?string $navigationGroup = 'Learning';

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    protected static ?string $navigationLabel = 'Community';

    protected static ?int $navigationSort = 30;

    protected static string $view = 'filament.learner.pages.community-launch';

    public function launch()
    {
        $connection = DiscourseConnection::where('is_active', true)->first();
        if (!$connection) {
            Notification::make()
                ->title('Community Forum Unavailable')
                ->body('The community forum connection is not configured.')
                ->danger()
                ->send();
            return;
        }

        return redirect()->away(rtrim($connection->base_url, '/') . '/session/sso');
    }
}
