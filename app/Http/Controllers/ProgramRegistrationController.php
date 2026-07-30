<?php

namespace App\Http\Controllers;

use App\Enums\ProgramRegistrationStatus;
use App\Models\Programs\Program;
use App\Models\Programs\ProgramRegistration;
use App\Services\Programs\ProgramRegistrationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class ProgramRegistrationController extends Controller
{
    public function __construct(
        private readonly ProgramRegistrationService $registrations,
    ) {}

    /** Step 1 — micro-form submit. */
    public function store(Request $request, Program $program): RedirectResponse
    {
        $edition = $program->currentEdition();

        abort_unless($edition && $edition->status->acceptsRegistrations(), 404);

        $data = $request->validate([
            'guardian_name' => ['required', 'string', 'max:120'],
            'guardian_email' => ['required', 'email', 'max:190'],
            'guardian_phone' => ['nullable', 'string', 'max:32'],
            'guardian_whatsapp' => ['nullable', 'string', 'max:32'],
            'participant_name' => ['required', 'string', 'max:120'],
            'participant_dob' => ['required', 'date', 'before:today'],
            'program_edition_track_id' => ['nullable', 'integer'],
            'sibling_group_uuid' => ['nullable', 'uuid'],
        ]);

        $data['source'] = $request->string('src', 'web')->toString() ?: 'web';
        $data['utm'] = collect($request->only(['utm_source', 'utm_medium', 'utm_campaign']))
            ->filter()
            ->all();

        $registration = $this->registrations->start($edition, $data);

        if ($registration->status === ProgramRegistrationStatus::Waitlisted) {
            return redirect()
                ->route('programs.registrations.status', $registration->uuid)
                ->with('flash', ['type' => 'info', 'message' => 'That track is currently full — you are on the waitlist.']);
        }

        return redirect()->route('programs.registrations.verify.page', $registration->uuid);
    }

    /** Step 2 — the "check your email" page with OTP entry. */
    public function verifyPage(ProgramRegistration $registration): Response|RedirectResponse
    {
        if ($registration->email_verified_at) {
            return redirect()->route('programs.registrations.status', $registration->uuid);
        }

        return Inertia::render('Public/Programs/Verify', [
            'registration' => $this->publicPayload($registration),
        ]);
    }

    /** Step 2 — link click from the email. */
    public function verifyByToken(ProgramRegistration $registration, string $token): RedirectResponse
    {
        if (! $registration->email_verified_at && ! $this->registrations->verifyByToken($registration, $token)) {
            return redirect()
                ->route('programs.registrations.verify.page', $registration->uuid)
                ->with('flash', ['type' => 'error', 'message' => 'That link has expired — enter the code from the email or request a new one.']);
        }

        return redirect()->route('programs.registrations.status', $registration->uuid);
    }

    /** Step 2 — OTP typed on the page. */
    public function verifyByOtp(Request $request, ProgramRegistration $registration): RedirectResponse
    {
        $request->validate(['otp' => ['required', 'digits:6']]);

        if ($registration->email_verified_at) {
            return redirect()->route('programs.registrations.status', $registration->uuid);
        }

        if (! $this->registrations->verifyByOtp($registration, $request->string('otp')->toString())) {
            throw ValidationException::withMessages(['otp' => 'That code is incorrect or has expired.']);
        }

        return redirect()->route('programs.registrations.status', $registration->uuid);
    }

    public function resend(ProgramRegistration $registration): RedirectResponse
    {
        if (! $registration->email_verified_at) {
            $this->registrations->sendVerification($registration);
        }

        return back()->with('flash', ['type' => 'success', 'message' => 'A fresh code is on its way — check spam too.']);
    }

    /** Funnel hub: shows the next step for this registration. */
    public function status(ProgramRegistration $registration): Response
    {
        return Inertia::render('Public/Programs/Status', [
            'registration' => $this->publicPayload($registration),
            'onboardingUrl' => $registration->status->isPaidOrBeyond()
                ? route('programs.onboarding.show', ['token' => $registration->resume_token])
                : null,
        ]);
    }

    /** Step 3 — seat hold + order, then off to the existing checkout. */
    public function pay(ProgramRegistration $registration): RedirectResponse
    {
        $order = $this->registrations->beginCheckout($registration);

        return redirect()->route('checkout.orders.review', $order->uuid);
    }

    private function publicPayload(ProgramRegistration $registration): array
    {
        $registration->load(['edition.program', 'track']);

        return [
            'uuid' => $registration->uuid,
            'status' => $registration->status->value,
            'guardianName' => $registration->guardian_name,
            'guardianEmail' => $registration->guardian_email,
            'participantName' => $registration->participant_name,
            'emailVerified' => (bool) $registration->email_verified_at,
            'programName' => $registration->edition->program->name,
            'editionTitle' => $registration->edition->title,
            'trackName' => $registration->track?->name,
            'programSlug' => $registration->edition->program->slug,
        ];
    }
}
