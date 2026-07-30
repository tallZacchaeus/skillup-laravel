// Build calendar links/files from real event start/end times. No fabricated data —
// callers only render these when an event has a start time.

function toUtcStamp(iso) {
    // → YYYYMMDDTHHMMSSZ (UTC), the format Google Calendar and ICS both expect.
    return new Date(iso).toISOString().replace(/[-:]/g, '').replace(/\.\d{3}/, '');
}

function fallbackEnd(startIso) {
    return new Date(new Date(startIso).getTime() + 60 * 60 * 1000).toISOString();
}

/** Google Calendar "add event" URL. */
export function googleCalendarUrl(event) {
    if (!event?.startsAt) return null;
    const start = toUtcStamp(event.startsAt);
    const end = toUtcStamp(event.endsAt || fallbackEnd(event.startsAt));
    const params = new URLSearchParams({
        action: 'TEMPLATE',
        text: event.title || 'SkillUp event',
        dates: `${start}/${end}`,
        details: event.summary || '',
        location: event.url || 'Online',
    });
    return `https://calendar.google.com/calendar/render?${params.toString()}`;
}

/** Trigger a download of an .ics file for the event. */
export function downloadIcs(event) {
    if (!event?.startsAt) return;
    const start = toUtcStamp(event.startsAt);
    const end = toUtcStamp(event.endsAt || fallbackEnd(event.startsAt));
    const stamp = toUtcStamp(new Date().toISOString());
    const escape = (text) => String(text || '').replace(/([,;\\])/g, '\\$1').replace(/\n/g, '\\n');

    const lines = [
        'BEGIN:VCALENDAR',
        'VERSION:2.0',
        'PRODID:-//SkillUp//Events//EN',
        'BEGIN:VEVENT',
        `UID:${event.slug || event.id || start}@skillup`,
        `DTSTAMP:${stamp}`,
        `DTSTART:${start}`,
        `DTEND:${end}`,
        `SUMMARY:${escape(event.title)}`,
        `DESCRIPTION:${escape(event.summary)}`,
        `URL:${escape(event.url)}`,
        'LOCATION:Online',
        'END:VEVENT',
        'END:VCALENDAR',
    ];

    const blob = new Blob([lines.join('\r\n')], { type: 'text/calendar;charset=utf-8' });
    const href = URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = href;
    link.download = `${event.slug || 'event'}.ics`;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    URL.revokeObjectURL(href);
}
