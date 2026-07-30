@extends('emails.programs.layout')

@section('content')
    <h1 style="margin:0 0 12px;font-size:22px;color:#14183E;">Payment received 🎉</h1>
    <p style="margin:0 0 20px;font-size:15px;line-height:24px;color:#334155;">
        <strong>{{ $registration->participant_name }}</strong>'s seat in
        <strong>{{ $registration->edition->title }}</strong> ({{ $registration->track?->name }}) is confirmed.
    </p>
    <p style="margin:0 0 20px;font-size:15px;line-height:24px;color:#334155;">
        One last step: complete the onboarding form — emergency contact, pickup authorization, and a few
        programme details. It takes about 3 minutes, and the Certificate of Participation can only be
        issued once it's done.
    </p>

    <table role="presentation" cellpadding="0" cellspacing="0" style="margin:0 0 24px;">
        <tr>
            <td style="border-radius:8px;background:#0D4EFF;">
                <a href="{{ $onboardingUrl }}" style="display:inline-block;padding:14px 28px;color:#ffffff;font-size:15px;font-weight:600;text-decoration:none;">
                    Complete onboarding
                </a>
            </td>
        </tr>
    </table>

    <p style="margin:0;font-size:13px;line-height:20px;color:#64748B;">
        You can return to this form anytime using this same link, on any device.
    </p>
@endsection
