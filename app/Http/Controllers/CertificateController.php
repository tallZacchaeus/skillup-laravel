<?php

namespace App\Http\Controllers;

use App\Models\Programs\ProgramCertificate;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CertificateController extends Controller
{
    /** Public verification page: paste a serial, see whether it's genuine. */
    public function verify(Request $request): Response
    {
        $serial = strtoupper(trim((string) $request->query('serial', '')));
        $certificate = null;
        $checked = $serial !== '';

        if ($checked) {
            $certificate = ProgramCertificate::query()
                ->where('serial', $serial)
                ->with('registration.track')
                ->first();
        }

        return Inertia::render('Public/Certificates/Verify', [
            'serial' => $serial,
            'checked' => $checked,
            'certificate' => $certificate ? $this->payload($certificate) : null,
        ]);
    }

    /** Printable certificate page (linked from the issuance email). */
    public function show(string $serial): Response
    {
        $certificate = ProgramCertificate::query()
            ->where('serial', strtoupper($serial))
            ->with('registration.track')
            ->firstOrFail();

        return Inertia::render('Public/Certificates/Show', [
            'certificate' => $this->payload($certificate),
        ]);
    }

    private function payload(ProgramCertificate $certificate): array
    {
        return [
            'serial' => $certificate->serial,
            'recipientName' => $certificate->recipient_name,
            'programTitle' => $certificate->program_title,
            'trackName' => $certificate->registration?->track?->name,
            'issuedOn' => $certificate->issued_on->format('F j, Y'),
            'verifyUrl' => route('certificates.verify', ['serial' => $certificate->serial]),
        ];
    }
}
