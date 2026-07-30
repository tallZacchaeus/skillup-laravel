<?php

namespace App\Http\Controllers;

use App\Models\Programs\ProgramRegistration;
use App\Services\Programs\ProgramRegistrationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProgramOnboardingController extends Controller
{
    public function __construct(
        private readonly ProgramRegistrationService $registrations,
    ) {}

    public function show(string $token): Response
    {
        $registration = $this->resolve($token);

        $registration->load(['edition.program', 'track']);

        return Inertia::render('Public/Programs/Onboarding', [
            'token' => $token,
            'registration' => [
                'uuid' => $registration->uuid,
                'status' => $registration->status->value,
                'guardianName' => $registration->guardian_name,
                'participantName' => $registration->participant_name,
                'participantGender' => $registration->participant_gender,
                'editionTitle' => $registration->edition->title,
                'trackName' => $registration->track?->name,
                'programSlug' => $registration->edition->program->slug,
                'profileCompleted' => (bool) $registration->profile_completed_at,
                'emergencyContactName' => $registration->emergency_contact_name,
                'emergencyContactPhone' => $registration->emergency_contact_phone,
                'firstAidConsent' => $registration->first_aid_consent,
                'mediaConsent' => $registration->media_consent,
                'customFields' => $registration->custom_fields ?? [],
            ],
            'fields' => $registration->edition->registration_fields ?? [],
        ]);
    }

    public function store(Request $request, string $token): RedirectResponse
    {
        $registration = $this->resolve($token);

        $data = $request->validate([
            'emergency_contact_name' => ['required', 'string', 'max:120'],
            'emergency_contact_phone' => ['required', 'string', 'max:32'],
            'medical_notes' => ['nullable', 'string', 'max:2000'],
            'authorized_pickups' => ['nullable', 'array', 'max:5'],
            'authorized_pickups.*.name' => ['required', 'string', 'max:120'],
            'authorized_pickups.*.phone' => ['required', 'string', 'max:32'],
            'participant_gender' => ['nullable', 'string', 'max:30'],
            'first_aid_consent' => ['accepted'],
            'guardian_consent' => ['accepted'],
            'media_consent' => ['nullable', 'boolean'],
            'custom_fields' => ['nullable', 'array'],
        ]);

        $registration = $this->registrations->completeProfile($registration, $data);

        $message = $registration->profile_completed_at
            ? 'All set — '.$registration->participant_name."'s onboarding is complete. See you at the programme!"
            : 'Saved. A few required answers are still missing — you can finish anytime with your link.';

        return redirect()
            ->route('programs.registrations.status', $registration->uuid)
            ->with('flash', ['type' => 'success', 'message' => $message]);
    }

    private function resolve(string $token): ProgramRegistration
    {
        abort_if(strlen($token) < 32, 404);

        return ProgramRegistration::query()
            ->where('resume_token', $token)
            ->firstOrFail();
    }
}
