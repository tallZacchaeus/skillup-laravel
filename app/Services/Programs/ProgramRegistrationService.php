<?php

namespace App\Services\Programs;

use App\Enums\ProgramRegistrationStatus;
use App\Jobs\Notifications\SendEmailMessageJob;
use App\Models\Catalog\Order;
use App\Models\Notifications\EmailMessage;
use App\Models\Programs\ProgramEdition;
use App\Models\Programs\ProgramEditionTrack;
use App\Models\Programs\ProgramRegistration;
use App\Services\Payments\CheckoutOrderService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ProgramRegistrationService
{
    public function __construct(
        private readonly CheckoutOrderService $checkout,
    ) {}

    /**
     * Step 1 — micro-form. Dedupe: a repeat submission for the same guardian +
     * ward resumes the existing registration instead of creating a new one.
     *
     * @param  array<string, mixed>  $data
     */
    public function start(ProgramEdition $edition, array $data): ProgramRegistration
    {
        $dob = \Illuminate\Support\Carbon::parse($data['participant_dob']);
        $age = (int) $dob->diffInYears($edition->ageReferenceDate());
        $track = isset($data['program_edition_track_id'])
            ? $edition->tracks->firstWhere('id', (int) $data['program_edition_track_id'])
            : $edition->trackForAge($age);

        if (! $track) {
            throw ValidationException::withMessages([
                'participant_dob' => "We couldn't match an age track for a {$age}-year-old. Ages are counted as of ".$edition->ageReferenceDate()->format('M j, Y').'.',
            ]);
        }

        if (! $track->acceptsAge($age)) {
            throw ValidationException::withMessages([
                'program_edition_track_id' => "{$track->name} accepts ages {$track->age_min}–{$track->age_max}; this ward will be {$age} on ".$edition->ageReferenceDate()->format('M j, Y').'.',
            ]);
        }

        $existing = ProgramRegistration::query()
            ->where('program_edition_id', $edition->id)
            ->where('guardian_email', strtolower(trim($data['guardian_email'])))
            ->where('participant_name', trim($data['participant_name']))
            ->whereDate('participant_dob', $dob->toDateString())
            ->first();

        if ($existing) {
            if (! $existing->email_verified_at) {
                $this->sendVerification($existing);
            }

            return $existing;
        }

        $registration = ProgramRegistration::create([
            'program_edition_id' => $edition->id,
            'program_edition_track_id' => $track->id,
            'user_id' => auth()->id(),
            'guardian_name' => trim($data['guardian_name']),
            'guardian_email' => strtolower(trim($data['guardian_email'])),
            'guardian_phone' => $data['guardian_phone'] ?? null,
            'guardian_whatsapp' => $data['guardian_whatsapp'] ?? $data['guardian_phone'] ?? null,
            'participant_name' => trim($data['participant_name']),
            'participant_dob' => $dob->toDateString(),
            'status' => $track->isFull() ? ProgramRegistrationStatus::Waitlisted : ProgramRegistrationStatus::Started,
            'sibling_group_uuid' => $data['sibling_group_uuid'] ?? null,
            'source' => $data['source'] ?? 'web',
            'utm' => $data['utm'] ?? [],
        ]);

        if ($registration->status === ProgramRegistrationStatus::Started) {
            $this->sendVerification($registration);
        }

        return $registration;
    }

    /** Step 2 — issue (or reissue) the verification link + OTP email. Returns the OTP (for tests). */
    public function sendVerification(ProgramRegistration $registration): string
    {
        $otp = (string) random_int(100000, 999999);

        $registration->forceFill([
            'email_verification_token' => Str::random(48),
            'email_verification_otp' => Hash::make($otp),
            'email_verification_expires_at' => now()->addMinutes(30),
        ])->save();

        $verifyUrl = route('programs.registrations.verify', [
            'registration' => $registration->uuid,
            'token' => $registration->email_verification_token,
        ]);

        $this->queueEmail(
            $registration,
            'Confirm your email — '.$registration->edition->title,
            view('emails.programs.verify', [
                'registration' => $registration,
                'verifyUrl' => $verifyUrl,
                'otp' => $otp,
            ])->render(),
        );

        return $otp;
    }

    public function verifyByToken(ProgramRegistration $registration, string $token): bool
    {
        if (
            ! $registration->email_verification_token
            || ! hash_equals($registration->email_verification_token, $token)
            || ! $registration->email_verification_expires_at?->isFuture()
        ) {
            return false;
        }

        $this->markVerified($registration);

        return true;
    }

    public function verifyByOtp(ProgramRegistration $registration, string $otp): bool
    {
        if (
            ! $registration->email_verification_otp
            || ! $registration->email_verification_expires_at?->isFuture()
            || ! Hash::check($otp, $registration->email_verification_otp)
        ) {
            return false;
        }

        $this->markVerified($registration);

        return true;
    }

    /**
     * Step 3 — place a seat hold and create the order for the ward's track.
     * The hold counts toward capacity, so the last seat cannot be sold twice.
     */
    public function beginCheckout(ProgramRegistration $registration): Order
    {
        if (! $registration->email_verified_at) {
            throw ValidationException::withMessages(['registration' => 'Confirm your email before payment.']);
        }

        if ($registration->status->isPaidOrBeyond()) {
            throw ValidationException::withMessages(['registration' => 'This registration is already paid.']);
        }

        $track = $registration->track;
        $product = $track?->product;

        if (! $track || ! $product) {
            throw ValidationException::withMessages(['registration' => 'This track is not open for payment yet.']);
        }

        $trackFull = false;

        try {
            return $this->beginCheckoutTransaction($registration, $track, $product, $trackFull);
        } catch (\RuntimeException $e) {
            if (! $trackFull) {
                throw $e;
            }

            // The waitlist write must survive the rolled-back transaction.
            $registration->forceFill([
                'status' => ProgramRegistrationStatus::Waitlisted,
                'seat_held_until' => null,
            ])->save();

            throw ValidationException::withMessages([
                'registration' => "{$track->name} just filled up — we've placed {$registration->participant_name} on the waitlist and will contact you if a seat opens.",
            ]);
        }
    }

    private function beginCheckoutTransaction(
        ProgramRegistration $registration,
        ProgramEditionTrack $track,
        $product,
        bool &$trackFull,
    ): Order {
        return DB::transaction(function () use ($registration, $track, $product, &$trackFull) {
            // Re-check capacity under lock; our own hold/seat doesn't block us.
            $lockedTrack = ProgramEditionTrack::query()->lockForUpdate()->find($track->id);
            $othersHolding = $lockedTrack->registrations()
                ->whereKeyNot($registration->id)
                ->where(function ($query) {
                    $query->whereIn('status', ProgramRegistrationStatus::seatConsumingValues())
                        ->orWhere('seat_held_until', '>', now());
                })
                ->count();

            if ($lockedTrack->capacity !== null && $othersHolding >= $lockedTrack->capacity) {
                $trackFull = true;

                throw new \RuntimeException('track_full');
            }

            $registration->forceFill([
                'seat_held_until' => now()->addMinutes($registration->edition->seat_hold_minutes),
                'status' => ProgramRegistrationStatus::PaymentPending,
            ])->save();

            if ($registration->order && $registration->order->payment_status->value === 'pending') {
                return $registration->order;
            }

            $order = $this->checkout->create($product, [
                'name' => $registration->guardian_name,
                'email' => $registration->guardian_email,
                'phone' => $registration->guardian_phone,
                'payment_mode' => 'full',
                'auto_discount' => true,
                'extra_metadata' => [
                    'program_registration_uuid' => $registration->uuid,
                    'participant_name' => $registration->participant_name,
                ],
            ]);

            $registration->update(['order_id' => $order->id]);

            return $order;
        });
    }

    /** Called when an order tied to a registration becomes fully paid. */
    public function handleOrderPaid(Order $order): void
    {
        $uuid = data_get($order->metadata, 'program_registration_uuid');

        if (! $uuid) {
            return;
        }

        $registration = ProgramRegistration::query()->where('uuid', $uuid)->first();

        if (! $registration || $registration->status->isPaidOrBeyond()) {
            return;
        }

        $registration->forceFill([
            'status' => ProgramRegistrationStatus::Paid,
            'order_id' => $order->id,
            'seat_held_until' => null,
        ])->save();

        $this->queueEmail(
            $registration,
            'Payment received — complete '.$registration->participant_name."'s onboarding",
            view('emails.programs.paid', [
                'registration' => $registration,
                'onboardingUrl' => route('programs.onboarding.show', ['token' => $registration->resume_token]),
            ])->render(),
        );
    }

    /**
     * Step 4 — post-payment onboarding form.
     *
     * @param  array<string, mixed>  $data
     */
    public function completeProfile(ProgramRegistration $registration, array $data): ProgramRegistration
    {
        if (! $registration->status->isPaidOrBeyond()) {
            throw ValidationException::withMessages(['registration' => 'Complete payment before onboarding.']);
        }

        $registration->forceFill([
            'emergency_contact_name' => $data['emergency_contact_name'],
            'emergency_contact_phone' => $data['emergency_contact_phone'],
            'medical_notes' => $data['medical_notes'] ?? null,
            'authorized_pickups' => $data['authorized_pickups'] ?? [],
            'first_aid_consent' => (bool) ($data['first_aid_consent'] ?? false),
            'media_consent' => (bool) ($data['media_consent'] ?? false),
            'guardian_consent_at' => $registration->guardian_consent_at ?? now(),
            'custom_fields' => $data['custom_fields'] ?? [],
            'participant_gender' => $data['participant_gender'] ?? $registration->participant_gender,
        ])->save();

        if ($registration->isProfileComplete()) {
            $registration->forceFill([
                'profile_completed_at' => $registration->profile_completed_at ?? now(),
                'status' => $registration->status === ProgramRegistrationStatus::Paid
                    ? ProgramRegistrationStatus::ProfileCompleted
                    : $registration->status,
                'resume_token' => Str::random(48),
            ])->save();
        }

        return $registration->refresh();
    }

    /**
     * Bank-transfer / cash payments recorded by an admin. Creates a paid
     * order + payment + receipt through the normal commerce tables so
     * reporting stays truthful, then confirms the seat.
     *
     * @param  array<string, mixed>  $data
     */
    public function recordOfflinePayment(ProgramRegistration $registration, array $data): Order
    {
        if ($registration->status->isPaidOrBeyond()) {
            throw ValidationException::withMessages(['registration' => 'This registration is already paid.']);
        }

        $product = $registration->track?->product;

        if (! $product) {
            throw ValidationException::withMessages(['registration' => 'This track has no product configured.']);
        }

        return DB::transaction(function () use ($registration, $product, $data) {
            $product->loadMissing('defaultPrice');
            $amount = (float) $data['amount'];
            $currency = $product->defaultPrice?->currency ?? 'NGN';

            $order = $registration->order;

            if (! $order) {
                $order = Order::create([
                    'user_id' => $registration->user_id,
                    'status' => \App\Enums\OrderStatus::Paid,
                    'payment_status' => \App\Enums\PaymentStatus::Paid,
                    'currency' => $currency,
                    'subtotal' => $amount,
                    'discount_total' => 0,
                    'tax_total' => 0,
                    'total' => $amount,
                    'amount_paid' => $amount,
                    'balance_due' => 0,
                    'payment_provider' => 'offline',
                    'paid_at' => now(),
                    'metadata' => [
                        'customer' => [
                            'name' => $registration->guardian_name,
                            'email' => $registration->guardian_email,
                            'phone' => $registration->guardian_phone,
                        ],
                        'program_registration_uuid' => $registration->uuid,
                        'offline' => ['reference' => $data['reference'] ?? null, 'note' => $data['note'] ?? null],
                    ],
                ]);

                $order->items()->create([
                    'product_id' => $product->id,
                    'product_price_id' => $product->defaultPrice?->id,
                    'product_title' => $product->title,
                    'quantity' => 1,
                    'unit_amount' => $amount,
                    'discount_amount' => 0,
                    'total' => $amount,
                    'metadata' => ['offline' => true],
                ]);
            }

            $payment = $order->payments()->create([
                'user_id' => $registration->user_id,
                'provider' => 'offline',
                'reference' => $data['reference'] ?? 'offline-'.Str::random(10),
                'status' => \App\Enums\PaymentStatus::Paid,
                'currency' => $currency,
                'amount' => $amount,
                'channel' => $data['channel'] ?? 'bank_transfer',
                'paid_at' => now(),
                'verified_at' => now(),
                'metadata' => ['recorded_by_user_id' => auth()->id(), 'note' => $data['note'] ?? null],
            ]);

            \App\Models\Catalog\Receipt::firstOrCreate(
                ['payment_id' => $payment->id],
                [
                    'order_id' => $order->id,
                    'currency' => $currency,
                    'amount' => $amount,
                    'issued_at' => now(),
                    'metadata' => ['provider' => 'offline'],
                ],
            );

            $registration->forceFill([
                'status' => ProgramRegistrationStatus::Paid,
                'order_id' => $order->id,
                'seat_held_until' => null,
            ])->save();

            $this->queueEmail(
                $registration,
                'Payment received — complete '.$registration->participant_name."'s onboarding",
                view('emails.programs.paid', [
                    'registration' => $registration,
                    'onboardingUrl' => route('programs.onboarding.show', ['token' => $registration->resume_token]),
                ])->render(),
            );

            return $order;
        });
    }

    /** Expired checkout holds release their seats lazily via queries; this clears stale rows. */
    public function releaseExpiredHolds(): int
    {
        return ProgramRegistration::query()
            ->where('seat_held_until', '<', now())
            ->whereNotIn('status', ProgramRegistrationStatus::seatConsumingValues())
            ->update(['seat_held_until' => null]);
    }

    /**
     * A refunded/cancelled paid order releases its seat and offers it to the
     * waitlist with a time-boxed (48h) payment window.
     */
    public function handleOrderRefunded(Order $order): void
    {
        $uuid = data_get($order->metadata, 'program_registration_uuid');

        if (! $uuid) {
            return;
        }

        $registration = ProgramRegistration::query()->where('uuid', $uuid)->first();

        if (! $registration || $registration->status === ProgramRegistrationStatus::Cancelled) {
            return;
        }

        $track = $registration->track;

        $registration->forceFill([
            'status' => ProgramRegistrationStatus::Cancelled,
            'seat_held_until' => null,
        ])->save();

        if ($track) {
            $this->promoteFromWaitlist($track);
        }
    }

    /** Offer the freed seat to the oldest waitlisted registration on the track. */
    public function promoteFromWaitlist(ProgramEditionTrack $track): ?ProgramRegistration
    {
        if ($track->isFull()) {
            return null;
        }

        $next = $track->registrations()
            ->where('status', ProgramRegistrationStatus::Waitlisted->value)
            ->whereNull('email_invalid_at')
            ->whereNull('metadata->waitlist_offer_lapsed_at') // lapsed offers go to the back; ops can re-offer manually
            ->orderBy('created_at')
            ->first();

        if (! $next) {
            return null;
        }

        $metadata = $next->metadata ?? [];
        $metadata['waitlist_offer_expires_at'] = now()->addHours(48)->toIso8601String();

        $next->forceFill([
            'status' => $next->email_verified_at
                ? ProgramRegistrationStatus::EmailVerified
                : ProgramRegistrationStatus::Started,
            'metadata' => $metadata,
        ])->save();

        if (! $next->email_verified_at) {
            $this->sendVerification($next);
        }

        $this->queueEmail(
            $next,
            'A seat just opened for '.$next->participant_name.'! (48 hours to claim)',
            view('emails.programs.waitlist-offer', [
                'registration' => $next,
                'statusUrl' => route('programs.registrations.status', $next->uuid),
            ])->render(),
        );

        return $next;
    }

    /** Re-waitlist promotions whose 48h window lapsed, and offer the seat onward. */
    public function expireWaitlistOffers(): int
    {
        $expired = ProgramRegistration::query()
            ->whereIn('status', [ProgramRegistrationStatus::Started->value, ProgramRegistrationStatus::EmailVerified->value])
            ->whereNotNull('metadata->waitlist_offer_expires_at')
            ->where('metadata->waitlist_offer_expires_at', '<=', now()->toIso8601String())
            ->with('track')
            ->get();

        foreach ($expired as $registration) {
            $metadata = $registration->metadata ?? [];
            unset($metadata['waitlist_offer_expires_at']);
            $metadata['waitlist_offer_lapsed_at'] = now()->toIso8601String();

            $registration->forceFill([
                'status' => ProgramRegistrationStatus::Waitlisted,
                'metadata' => $metadata,
            ])->save();

            if ($registration->track) {
                $this->promoteFromWaitlist($registration->track);
            }
        }

        return $expired->count();
    }

    private function markVerified(ProgramRegistration $registration): void
    {
        $registration->forceFill([
            'email_verified_at' => now(),
            'email_invalid_at' => null,
            'email_verification_token' => null,
            'email_verification_otp' => null,
            'email_verification_expires_at' => null,
            'status' => $registration->status === ProgramRegistrationStatus::Started
                ? ProgramRegistrationStatus::EmailVerified
                : $registration->status,
        ])->save();
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
