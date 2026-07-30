<?php

namespace App\Http\Controllers;

use App\Models\Content\Event;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class CommunityController extends Controller
{
    /** Event types surfaced on the community page, with friendly labels. */
    private const TYPE_LABELS = [
        'webinar' => 'Webinar',
        'workshop' => 'Workshop',
        'meetup' => 'Meetup',
        'office_hours' => 'Office hours',
        'hackathon' => 'Hackathon',
    ];

    public function index(): Response
    {
        // Real upcoming events only — the section hides itself when this is empty.
        $events = Event::query()
            ->whereIn('status', ['upcoming', 'live'])
            ->orderBy('starts_at')
            ->limit(3)
            ->get()
            ->map(fn (Event $event) => $this->eventCard($event))
            ->all();

        return Inertia::render('Public/Community', [
            'events' => $events,
        ]);
    }

    /**
     * Shapes an event for the shared EventCard component (same field contract
     * the Events page uses), so the card is reused rather than duplicated.
     *
     * @return array<string, mixed>
     */
    private function eventCard(Event $event): array
    {
        $start = $event->starts_at;
        $end = $event->ends_at;
        $limit = $event->registration_limit;
        $seatsRemaining = $limit !== null ? max(0, $limit - (int) $event->registered_count) : null;

        return [
            'id' => $event->id,
            'title' => $event->title,
            'slug' => $event->slug,
            'summary' => Str::limit(strip_tags((string) $event->description), 160),
            'image' => '/images/consistent.jpg',
            'category' => ['value' => $event->type, 'label' => self::TYPE_LABELS[$event->type] ?? Str::headline((string) $event->type)],
            'dateLabel' => $start?->translatedFormat('D, M j, Y'),
            'timeLabel' => $start?->translatedFormat('g:i A'),
            'duration' => $this->duration($start, $end),
            'deliveryMode' => 'Online',
            'status' => $event->status,
            'seatsRemaining' => $seatsRemaining,
            'isFull' => $seatsRemaining !== null && $seatsRemaining === 0,
            'url' => route('events.show', $event->slug),
            'recordingUrl' => $event->recording_url,
        ];
    }

    private function duration($start, $end): ?string
    {
        if (! $start || ! $end) {
            return null;
        }

        $minutes = (int) round($start->diffInMinutes($end, true));
        if ($minutes <= 0) {
            return null;
        }
        if ($minutes < 60) {
            return $minutes.' min';
        }

        $hours = intdiv($minutes, 60);
        $rest = $minutes % 60;

        return $rest === 0 ? $hours.' hr' : $hours.' hr '.$rest.' min';
    }
}
