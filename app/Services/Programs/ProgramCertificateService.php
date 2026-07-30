<?php

namespace App\Services\Programs;

use App\Jobs\Notifications\SendEmailMessageJob;
use App\Models\Notifications\EmailMessage;
use App\Models\Programs\ProgramCertificate;
use App\Models\Programs\ProgramRegistration;
use Illuminate\Validation\ValidationException;

class ProgramCertificateService
{
    /**
     * Issue a Certificate of Participation. Hard-gated: the registration must
     * be completed AND have a fully completed onboarding profile.
     */
    public function issue(ProgramRegistration $registration): ProgramCertificate
    {
        if (! $registration->isCertificateEligible()) {
            throw ValidationException::withMessages([
                'registration' => "{$registration->participant_name} is not certificate-eligible yet — the registration must be completed and the onboarding form finished.",
            ]);
        }

        if ($registration->certificate) {
            return $registration->certificate;
        }

        $certificate = ProgramCertificate::create([
            'program_registration_id' => $registration->id,
            'recipient_name' => $registration->participant_name,
            'program_title' => $registration->edition->title,
            'issued_on' => now()->toDateString(),
        ]);

        $message = EmailMessage::create([
            'user_id' => $registration->user_id,
            'recipient_email' => $registration->guardian_email,
            'subject' => $registration->participant_name."'s Certificate of Participation 🎓",
            'body_html' => view('emails.programs.certificate', [
                'registration' => $registration,
                'certificate' => $certificate,
                'certificateUrl' => route('certificates.show', $certificate->serial),
            ])->render(),
            'status' => 'queued',
        ]);

        SendEmailMessageJob::dispatch($message);

        return $certificate;
    }

    /**
     * Issue for every eligible registration of an edition; returns counts.
     *
     * @return array{issued: int, skipped: int}
     */
    public function issueEligibleForRegistrations(iterable $registrations): array
    {
        $issued = 0;
        $skipped = 0;

        foreach ($registrations as $registration) {
            if ($registration->isCertificateEligible()) {
                $wasNew = $registration->certificate === null;
                $this->issue($registration);
                $issued += $wasNew ? 1 : 0;
            } else {
                $skipped++;
            }
        }

        return ['issued' => $issued, 'skipped' => $skipped];
    }
}
