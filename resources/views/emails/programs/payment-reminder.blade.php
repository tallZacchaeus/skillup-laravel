@extends('emails.programs.layout')

@section('content')
    <h1 style="margin:0 0 12px;font-size:22px;color:#14183E;">{{ $registration->participant_name }}'s seat is waiting</h1>
    <p style="margin:0 0 20px;font-size:15px;line-height:24px;color:#334155;">
        Your email is confirmed — the last step to secure a place in
        <strong>{{ $registration->edition->title }}</strong> ({{ $registration->track?->name }}) is payment.
        @if ($seatsLeft !== null)
            Only <strong>{{ $seatsLeft }} {{ Str::plural('seat', $seatsLeft) }}</strong> remain on this track.
        @endif
    </p>

    <table role="presentation" cellpadding="0" cellspacing="0" style="margin:0 0 24px;">
        <tr>
            <td style="border-radius:8px;background:#0D4EFF;">
                <a href="{{ $statusUrl }}" style="display:inline-block;padding:14px 28px;color:#ffffff;font-size:15px;font-weight:600;text-decoration:none;">
                    Continue to payment
                </a>
            </td>
        </tr>
    </table>

    <p style="margin:0;font-size:13px;line-height:20px;color:#64748B;">
        Questions? Reply to this email or message us on WhatsApp — we're happy to help.
    </p>
@endsection
