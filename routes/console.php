<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('skillup:installment-reminders')->dailyAt('08:00')->withoutOverlapping();
Schedule::command('skillup:moodle-import')->dailyAt('01:00')->withoutOverlapping();
Schedule::command('skillup:moodle-reconcile')->weeklyOn(1, '02:00')->withoutOverlapping();
Schedule::command('queue:prune-failed --hours=720')->dailyAt('03:00');
Schedule::command('programs:send-nudges')->hourly()->withoutOverlapping();
Schedule::command('programs:purge-safeguarding-data')->monthlyOn(1, '04:00')->withoutOverlapping();
Schedule::command('model:prune', ['--model' => [\App\Models\Platform\PageVisit::class]])->dailyAt('03:30');
