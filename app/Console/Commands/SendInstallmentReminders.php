<?php

namespace App\Console\Commands;

use App\Models\Catalog\Installment;
use App\Notifications\InstallmentReminderNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;

class SendInstallmentReminders extends Command
{
    protected $signature = 'skillup:installment-reminders {--dry-run : Count reminders without sending notifications}';

    protected $description = 'Send email reminders for pending installments due soon.';

    public function handle(): int
    {
        $sent = 0;
        $skipped = 0;
        $dryRun = (bool) $this->option('dry-run');

        Installment::query()
            ->dueForReminder()
            ->with(['order.user', 'paymentPlan'])
            ->chunkById(100, function ($installments) use (&$sent, &$skipped, $dryRun): void {
                foreach ($installments as $installment) {
                    $email = $installment->order?->user?->email
                        ?? data_get($installment->order?->metadata, 'customer.email');

                    if (blank($email)) {
                        $skipped++;

                        continue;
                    }

                    if (! $dryRun) {
                        $user = $installment->order?->user;
                        if ($user) {
                            $user->notify(new InstallmentReminderNotification($installment));
                        } else {
                            Notification::route('mail', $email)
                                ->notify(new InstallmentReminderNotification($installment));
                        }

                        $installment->update([
                            'reminder_sent_at' => now(),
                            'metadata' => array_merge($installment->metadata ?? [], [
                                'last_reminder_email' => $email,
                                'last_reminder_sent_at' => now()->toISOString(),
                            ]),
                        ]);
                    }

                    $sent++;
                }
            });

        $this->info(($dryRun ? 'Found' : 'Sent').' '.$sent.' installment reminder(s).');

        if ($skipped > 0) {
            $this->warn('Skipped '.$skipped.' installment(s) without a customer email.');
        }

        return self::SUCCESS;
    }
}
