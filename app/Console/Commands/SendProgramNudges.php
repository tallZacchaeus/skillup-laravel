<?php

namespace App\Console\Commands;

use App\Enums\ProgramRegistrationStatus;
use App\Jobs\Notifications\SendEmailMessageJob;
use App\Models\Notifications\EmailMessage;
use App\Models\Programs\ProgramRegistration;
use App\Services\Programs\ProgramRegistrationService;
use Illuminate\Console\Command;

/**
 * Funnel drip nudges:
 *  - started + unverified for 1h+  → resend the verification email (once).
 *  - verified but unpaid for 24h+  → payment reminder with seats-left urgency (once).
 *  - paid but profile incomplete   → weekly onboarding reminder.
 * Registrations with an invalid email (hard bounce) are skipped — those are
 * surfaced in admin for WhatsApp follow-up instead.
 */
class SendProgramNudges extends Command
{
    protected $signature = 'programs:send-nudges';

    protected $description = 'Send program registration funnel nudges and release expired seat holds';

    public function handle(ProgramRegistrationService $registrations): int
    {
        $released = $registrations->releaseExpiredHolds();
        $lapsedOffers = $registrations->expireWaitlistOffers();
        $sent = 0;

        // 1. Unverified after 1 hour → one automatic re-send.
        ProgramRegistration::query()
            ->where('status', ProgramRegistrationStatus::Started->value)
            ->whereNull('email_verified_at')
            ->whereNull('email_invalid_at')
            ->where('created_at', '<=', now()->subHour())
            ->whereNull('metadata->nudges->verification')
            ->each(function (ProgramRegistration $registration) use ($registrations, &$sent) {
                $registrations->sendVerification($registration);
                $this->stampNudge($registration, 'verification');
                $sent++;
            });

        // 2. Verified but unpaid after 24 hours → payment reminder.
        ProgramRegistration::query()
            ->whereIn('status', [ProgramRegistrationStatus::EmailVerified->value, ProgramRegistrationStatus::PaymentPending->value])
            ->whereNotNull('email_verified_at')
            ->whereNull('email_invalid_at')
            ->where('email_verified_at', '<=', now()->subDay())
            ->whereNull('metadata->nudges->payment')
            ->with(['edition.program', 'track'])
            ->each(function (ProgramRegistration $registration) use (&$sent) {
                $seatsLeft = $registration->track?->seatsRemaining();

                $this->queueEmail(
                    $registration,
                    'Seats are filling — secure '.$registration->participant_name."'s spot",
                    view('emails.programs.payment-reminder', [
                        'registration' => $registration,
                        'seatsLeft' => $seatsLeft,
                        'statusUrl' => route('programs.registrations.status', $registration->uuid),
                    ])->render(),
                );
                $this->stampNudge($registration, 'payment');
                $sent++;
            });

        // 3. Paid but onboarding incomplete → weekly reminder.
        ProgramRegistration::query()
            ->where('status', ProgramRegistrationStatus::Paid->value)
            ->whereNull('profile_completed_at')
            ->whereNull('email_invalid_at')
            ->where(function ($query) {
                $query->whereNull('metadata->nudges->onboarding_at')
                    ->orWhere('metadata->nudges->onboarding_at', '<=', now()->subWeek()->toIso8601String());
            })
            ->where('updated_at', '<=', now()->subDay())
            ->with(['edition.program', 'track'])
            ->each(function (ProgramRegistration $registration) use (&$sent) {
                $this->queueEmail(
                    $registration,
                    'One step left — complete '.$registration->participant_name."'s onboarding",
                    view('emails.programs.onboarding-reminder', [
                        'registration' => $registration,
                        'onboardingUrl' => route('programs.onboarding.show', ['token' => $registration->resume_token]),
                    ])->render(),
                );
                $this->stampNudge($registration, 'onboarding_at', now()->toIso8601String());
                $sent++;
            });

        $this->info("Released {$released} expired holds; lapsed {$lapsedOffers} waitlist offers; sent {$sent} nudges.");

        return self::SUCCESS;
    }

    private function stampNudge(ProgramRegistration $registration, string $key, mixed $value = true): void
    {
        $metadata = $registration->metadata ?? [];
        $metadata['nudges'][$key] = $value === true ? now()->toIso8601String() : $value;
        $registration->forceFill(['metadata' => $metadata])->save();
    }

    private function queueEmail(ProgramRegistration $registration, string $subject, string $html): void
    {
        $message = EmailMessage::create([
            'user_id' => $registration->user_id,
            'recipient_email' => $registration->guardian_email,
            'subject' => $subject,
            'body_html' => $html,
            'status' => 'queued',
        ]);

        SendEmailMessageJob::dispatch($message);
    }
}
