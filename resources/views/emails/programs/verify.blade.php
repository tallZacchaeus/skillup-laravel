@extends('emails.programs.layout')

@section('content')
    <h1 style="margin:0 0 12px;font-size:22px;color:#14183E;">Confirm your email, {{ $registration->guardian_name }}</h1>
    <p style="margin:0 0 20px;font-size:15px;line-height:24px;color:#334155;">
        You're one step from securing <strong>{{ $registration->participant_name }}</strong>'s spot in
        <strong>{{ $registration->edition->title }}</strong>. Confirm this email address to continue to payment.
    </p>

    <table role="presentation" cellpadding="0" cellspacing="0" style="margin:0 0 24px;">
        <tr>
            <td style="border-radius:8px;background:#0D4EFF;">
                <a href="{{ $verifyUrl }}" style="display:inline-block;padding:14px 28px;color:#ffffff;font-size:15px;font-weight:600;text-decoration:none;">
                    Confirm email &amp; continue
                </a>
            </td>
        </tr>
    </table>

    <p style="margin:0 0 8px;font-size:14px;color:#334155;">Or type this code on the confirmation page:</p>
    <p style="margin:0 0 24px;font-size:30px;font-weight:700;letter-spacing:8px;color:#14183E;">{{ $otp }}</p>

    <p style="margin:0;font-size:13px;line-height:20px;color:#64748B;">
        The link and code expire in 30 minutes. If you didn't request this, you can safely ignore this email.
    </p>
@endsection
