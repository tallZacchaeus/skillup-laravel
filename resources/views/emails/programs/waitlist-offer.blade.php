@extends('emails.programs.layout')

@section('content')
    <h1 style="margin:0 0 12px;font-size:22px;color:#14183E;">A seat just opened up! 🎉</h1>
    <p style="margin:0 0 20px;font-size:15px;line-height:24px;color:#334155;">
        Good news, {{ $registration->guardian_name }} — a seat in
        <strong>{{ $registration->edition->title }}</strong> ({{ $registration->track?->name }}) has become
        available for <strong>{{ $registration->participant_name }}</strong>.
    </p>
    <p style="margin:0 0 20px;font-size:15px;line-height:24px;color:#334155;">
        This offer is held for <strong>48 hours</strong>. Complete payment before then to secure the seat —
        after that it passes to the next family on the waitlist.
    </p>

    <table role="presentation" cellpadding="0" cellspacing="0" style="margin:0 0 24px;">
        <tr>
            <td style="border-radius:8px;background:#0D4EFF;">
                <a href="{{ $statusUrl }}" style="display:inline-block;padding:14px 28px;color:#ffffff;font-size:15px;font-weight:600;text-decoration:none;">
                    Claim the seat
                </a>
            </td>
        </tr>
    </table>

    <p style="margin:0;font-size:13px;line-height:20px;color:#64748B;">
        Questions? Reply to this email or reach us on WhatsApp.
    </p>
@endsection
