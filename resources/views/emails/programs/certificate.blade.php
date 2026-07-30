@extends('emails.programs.layout')

@section('content')
    <h1 style="margin:0 0 12px;font-size:22px;color:#14183E;">{{ $registration->participant_name }} did it! 🎓</h1>
    <p style="margin:0 0 20px;font-size:15px;line-height:24px;color:#334155;">
        Congratulations — <strong>{{ $registration->participant_name }}</strong> has completed
        <strong>{{ $certificate->program_title }}</strong>, and their Certificate of Participation is ready.
    </p>

    <table role="presentation" cellpadding="0" cellspacing="0" style="margin:0 0 24px;">
        <tr>
            <td style="border-radius:8px;background:#0D4EFF;">
                <a href="{{ $certificateUrl }}" style="display:inline-block;padding:14px 28px;color:#ffffff;font-size:15px;font-weight:600;text-decoration:none;">
                    View &amp; print the certificate
                </a>
            </td>
        </tr>
    </table>

    <p style="margin:0;font-size:13px;line-height:20px;color:#64748B;">
        Verification serial: <strong style="font-family:monospace;">{{ $certificate->serial }}</strong> —
        anyone can confirm this certificate at {{ route('certificates.verify') }}.
    </p>
@endsection
