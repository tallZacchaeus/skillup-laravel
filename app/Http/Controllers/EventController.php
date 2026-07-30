<?php

namespace App\Http\Controllers;

use App\Models\Content\Event;
use App\Notifications\EventRegisteredNotification;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class EventController extends Controller
{
    private const PER_PAGE = 9;

    private const TYPE_LABELS = [
        'webinar' => 'Webinar',
        'workshop' => 'Workshop',
        'info_session' => 'Info Session',
        'masterclass' => 'Masterclass',
        'bootcamp' => 'Bootcamp',
    ];

    public function index(Request $request): Response
    {
        $search = trim((string) $request->query('search', ''));
        $category = (string) $request->query('category', '');
        $view = in_array($request->query('view'), ['past'], true) ? 'past' : 'upcoming';
        $page = max(1, (int) $request->query('page', 1));

        $query = Event::query()
            ->where('status', '!=', 'cancelled')
            ->when($view === 'upcoming', fn (Builder $q) => $q->whereIn('status', ['upcoming', 'live'])->orderBy('starts_at'))
            ->when($view === 'past', fn (Builder $q) => $q->where('status', 'completed')->orderByDesc('starts_at'))
            ->when($category !== '', fn (Builder $q) => $q->where('type', $category))
            ->when($search !== '', function (Builder $q) use ($search) {
                $term = '%'.str_replace(['%', '_'], ['\%', '\_'], $search).'%';
                $q->where(fn (Builder $inner) => $inner->where('title', 'like', $term)->orWhere('description', 'like', $term));
            });

        /** @var LengthAwarePaginator $paginator */
        $paginator = $query->paginate(self::PER_PAGE, ['*'], 'page', $page)->withQueryString();

        $featured = null;
        if ($view === 'upcoming' && $search === '' && $category === '' && $page === 1) {
            $featured = Event::whereIn('status', ['upcoming', 'live'])->orderBy('starts_at')->first();
        }

        $featuredId = $featured?->id;
        $events = $paginator->getCollection()
            ->reject(fn (Event $event) => $event->id === $featuredId)
            ->map(fn (Event $event) => $this->formatEvent($event))
            ->values();

        return Inertia::render('Public/Events/Index', [
            'events' => $events,
            'featuredEvent' => $featured ? $this->formatEvent($featured) : null,
            'categories' => $this->categories(),
            'filters' => ['search' => $search, 'category' => $category, 'view' => $view],
            'hasPast' => Event::where('status', 'completed')->exists(),
            'pagination' => [
                'currentPage' => $paginator->currentPage(),
                'lastPage' => $paginator->lastPage(),
                'perPage' => self::PER_PAGE,
                'total' => $paginator->total(),
                'from' => $paginator->firstItem(),
                'to' => $paginator->lastItem(),
            ],
            'seo' => $this->seo($request, $category, $search),
        ]);
    }

    public function show(string $slug): Response
    {
        $event = Event::where('slug', $slug)->firstOrFail();

        return Inertia::render('Public/Events/Show', [
            'event' => $this->formatEvent($event, full: true),
            'structuredData' => $this->eventSchema($event),
        ]);
    }

    public function register(Request $request, string $slug)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:50',
        ]);

        [$event, $registration] = DB::transaction(function () use ($slug, $validated) {
            $event = Event::where('slug', $slug)->lockForUpdate()->firstOrFail();

            if ($event->status !== 'upcoming') {
                throw ValidationException::withMessages(['message' => 'Registrations are only allowed for upcoming events.']);
            }

            $currentRegistrations = $event->registrations()->count();

            if ($event->registration_limit !== null && $currentRegistrations >= $event->registration_limit) {
                throw ValidationException::withMessages(['message' => 'This event has reached its registration limit.']);
            }

            if ($event->registrations()->where('email', $validated['email'])->exists()) {
                throw ValidationException::withMessages(['email' => 'You have already registered for this event.']);
            }

            $registration = $event->registrations()->create([
                'user_id' => auth()->id(),
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? null,
            ]);

            $event->refreshRegisteredCount();

            return [$event->refresh(), $registration];
        });

        try {
            Notification::route('mail', $validated['email'])
                ->notify(new EventRegisteredNotification($event, $registration));
        } catch (\Exception $e) {
            logger()->error('Failed to send event registration email: '.$e->getMessage());
        }

        return redirect()->back()->with('success', 'You have successfully registered for the event!');
    }

    /**
     * @return array<string, mixed>
     */
    private function formatEvent(Event $event, bool $full = false): array
    {
        $start = $event->starts_at;
        $end = $event->ends_at;
        $limit = $event->registration_limit;
        $registered = (int) $event->registered_count;
        $seatsRemaining = $limit !== null ? max(0, $limit - $registered) : null;

        $data = [
            'id' => $event->id,
            'title' => $event->title,
            'slug' => $event->slug,
            'summary' => Str::limit(strip_tags((string) $event->description), 160),
            'image' => '/images/consistent.jpg',
            'category' => ['value' => $event->type, 'label' => self::TYPE_LABELS[$event->type] ?? Str::headline((string) $event->type)],
            'startsAt' => $start?->toIso8601String(),
            'endsAt' => $end?->toIso8601String(),
            'dateLabel' => $start?->translatedFormat('D, M j, Y'),
            'timeLabel' => $start?->translatedFormat('g:i A'),
            'duration' => $this->duration($start, $end),
            'deliveryMode' => 'Online',
            'status' => $event->status,
            'isUpcoming' => $event->status === 'upcoming',
            'seatsRemaining' => $seatsRemaining,
            'isFull' => $seatsRemaining !== null && $seatsRemaining === 0,
            'registerUrl' => route('events.register', $event->slug),
            'url' => route('events.show', $event->slug),
            'recordingUrl' => $event->recording_url,
        ];

        if ($full) {
            $data['description'] = $event->description;
        }

        return $data;
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

    /**
     * @return \Illuminate\Support\Collection<int, array<string, mixed>>
     */
    private function categories()
    {
        return Event::where('status', '!=', 'cancelled')
            ->select('type', DB::raw('count(*) as total'))
            ->groupBy('type')
            ->get()
            ->map(fn ($row) => [
                'value' => $row->type,
                'label' => self::TYPE_LABELS[$row->type] ?? Str::headline((string) $row->type),
                'count' => (int) $row->total,
            ])
            ->sortBy('label')
            ->values();
    }

    /**
     * @return array<string, string|null>
     */
    private function seo(Request $request, string $category, string $search): array
    {
        $title = 'Events & Webinars';
        $description = 'Join free SkillUp webinars, workshops, and info sessions — practical, live learning for Africa’s tech talent.';

        if ($category !== '') {
            $label = self::TYPE_LABELS[$category] ?? Str::headline($category);
            $title = $label.'s';
        } elseif ($search !== '') {
            $title = 'Search: '.$search;
        }

        return [
            'title' => $title,
            'description' => $description,
            'canonical' => $request->url(),
            'ogImage' => url('/images/hero.jpg'),
        ];
    }

    /**
     * @return array<int, array<string, mixed>>|null
     */
    private function eventSchema(Event $event): ?array
    {
        if (! $event->starts_at) {
            return null;
        }

        $url = route('events.show', $event->slug);

        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'Event',
            'name' => $event->title,
            'description' => Str::limit(strip_tags((string) $event->description), 300),
            'startDate' => $event->starts_at->toIso8601String(),
            'eventAttendanceMode' => 'https://schema.org/OnlineEventAttendanceMode',
            'eventStatus' => 'https://schema.org/EventScheduled',
            'url' => $url,
            'location' => ['@type' => 'VirtualLocation', 'url' => $url],
            'organizer' => ['@type' => 'Organization', 'name' => config('app.name', 'SkillUp'), 'url' => url('/')],
        ];

        if ($event->ends_at) {
            $schema['endDate'] = $event->ends_at->toIso8601String();
        }

        $breadcrumb = [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'Events', 'item' => route('events.index')],
                ['@type' => 'ListItem', 'position' => 2, 'name' => $event->title, 'item' => $url],
            ],
        ];

        return [$schema, $breadcrumb];
    }
}
