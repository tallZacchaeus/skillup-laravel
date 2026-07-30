<?php

namespace App\Console\Commands;

use App\Enums\ProgramEditionStatus;
use App\Models\Programs\ProgramEdition;
use App\Models\Programs\ProgramRegistration;
use Illuminate\Console\Command;

/**
 * NDPA retention: medical notes and authorized-pickup lists are only needed
 * while a programme runs. N months after an edition completes (per-edition
 * setting, default 6), they are erased. Alumni history (names, track,
 * certificate) is retained.
 */
class PurgeProgramSafeguardingData extends Command
{
    protected $signature = 'programs:purge-safeguarding-data {--dry-run : Report what would be purged without changing anything}';

    protected $description = 'Erase safeguarding data (medical notes, pickup lists) for editions past their retention window';

    public function handle(): int
    {
        $purged = 0;

        ProgramEdition::query()
            ->whereIn('status', [ProgramEditionStatus::Completed->value, ProgramEditionStatus::Archived->value])
            ->whereNotNull('ends_on')
            ->get()
            ->each(function (ProgramEdition $edition) use (&$purged) {
                $cutoff = $edition->ends_on->copy()->addMonths($edition->safeguarding_retention_months ?? 6);

                if ($cutoff->isFuture()) {
                    return;
                }

                $pending = $edition->registrations()
                    ->whereNull('safeguarding_purged_at')
                    ->where(function ($query) {
                        $query->whereNotNull('medical_notes')->orWhereNotNull('authorized_pickups');
                    });

                $count = (clone $pending)->count();

                if ($count === 0) {
                    return;
                }

                if ($this->option('dry-run')) {
                    $this->line("[dry-run] {$edition->title}: would purge {$count} registration(s).");

                    return;
                }

                $pending->get()->each(function (ProgramRegistration $registration) {
                    $registration->forceFill([
                        'medical_notes' => null,
                        'authorized_pickups' => null,
                        'safeguarding_purged_at' => now(),
                    ])->save();
                });

                $this->line("{$edition->title}: purged {$count} registration(s).");
                $purged += $count;
            });

        $this->info($this->option('dry-run') ? 'Dry run complete.' : "Purged safeguarding data for {$purged} registration(s).");

        return self::SUCCESS;
    }
}
