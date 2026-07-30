<?php

namespace App\Http\Controllers;

use App\Enums\EnrollmentStatus;
use App\Enums\ProgramEditionStatus;
use App\Models\Catalog\Enrollment;
use App\Models\Catalog\Product;
use App\Models\Content\Event;
use App\Models\Programs\Program;
use App\Models\Programs\ProgramCertificate;
use App\Support\Catalog\CatalogPresenter;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __construct(private readonly CatalogPresenter $presenter) {}

    public function __invoke(Request $request): Response
    {
        $user = $request->user();

        $enrollments = $user->enrollments()
            ->with(['product' => fn ($q) => $q->with($this->presenter->productRelations())])
            ->latest()
            ->get()
            ->filter(fn (Enrollment $e) => $e->product !== null);

        $courses = $enrollments->map(function (Enrollment $e) {
            $accessible = in_array($e->status, [EnrollmentStatus::Active, EnrollmentStatus::Completed], true);

            return [
                ...$this->presenter->formatProduct($e->product),
                'enrollmentStatus' => $e->status->value,
                'statusLabel' => Str::headline($e->status->value),
                'enrolledAt' => $e->created_at?->toFormattedDateString(),
                'accessible' => $accessible,
                'pendingReason' => $accessible ? null : $this->pendingReason($e),
            ];
        })->values();

        $enrolledIds = $enrollments->pluck('product_id')->all();
        $activeCount = $enrollments->where('status', EnrollmentStatus::Active)->count();
        $completedCount = $enrollments->where('status', EnrollmentStatus::Completed)->count();

        // Certificates the learner has genuinely earned (via their program registrations).
        $certificates = ProgramCertificate::query()
            ->whereHas('registration', fn ($q) => $q->where('user_id', $user->id))
            ->with('registration.edition.program')
            ->latest('issued_on')
            ->get()
            ->map(fn (ProgramCertificate $c) => [
                'serial' => $c->serial,
                'programName' => $c->registration?->edition?->program?->name ?? $c->registration?->edition?->title ?? 'SkillUp programme',
                'issuedOn' => $c->issued_on?->toFormattedDateString(),
                'showUrl' => route('certificates.show', $c->serial),
                'verifyUrl' => route('certificates.verify', ['serial' => $c->serial]),
            ])
            ->values();

        // Real upcoming events (empty → the widget hides itself).
        $events = Event::query()
            ->whereIn('status', ['upcoming', 'live'])
            ->orderBy('starts_at')
            ->limit(3)
            ->get()
            ->map(fn (Event $event) => $this->eventCard($event))
            ->values();

        // Recommendations: published courses the learner is not already enrolled in.
        $recommendations = Product::published()
            ->whereNotIn('id', $enrolledIds ?: [0])
            ->with($this->presenter->productRelations())
            ->orderByDesc('students_count')
            ->limit(4)
            ->get()
            ->map(fn (Product $p) => $this->presenter->formatProduct($p))
            ->values();

        // A featured programme to explore (real, open edition only).
        $program = Program::where('is_active', true)->orderBy('sort_order')->get()
            ->first(fn (Program $p) => $p->currentEdition()?->status === ProgramEditionStatus::RegistrationOpen);
        $featuredProgram = null;
        if ($program && ($edition = $program->currentEdition())) {
            $featuredProgram = [
                'slug' => $program->slug,
                'name' => $program->name,
                'tagline' => $program->tagline,
                'heroImagePath' => $edition->hero_image_path,
                'startsOn' => $edition->starts_on?->toFormattedDateString(),
            ];
        }

        // Real in-app notifications (Laravel Notifiable). Empty → widget shows a calm caught-up state.
        $notifications = $user->notifications()->latest()->limit(6)->get()->map(function ($n) {
            $data = is_array($n->data) ? $n->data : (json_decode($n->data, true) ?: []);

            return [
                'id' => $n->id,
                'title' => $data['title'] ?? Str::headline(class_basename($n->type)),
                'body' => $data['body'] ?? $data['message'] ?? null,
                'url' => $data['url'] ?? null,
                'read' => $n->read_at !== null,
                'createdAt' => $n->created_at?->diffForHumans(),
            ];
        })->values();

        return Inertia::render('Public/Dashboard', [
            'courses' => $courses,
            'metrics' => [
                'activeCourses' => $activeCount,
                'completedCourses' => $completedCount,
                'wishlist' => $user->wishlistProducts()->count(),
                'certificates' => $certificates->count(),
                'upcomingEvents' => $events->count(),
            ],
            'certificates' => $certificates,
            'events' => $events,
            'recommendations' => $recommendations,
            'featuredProgram' => $featuredProgram,
            'notifications' => $notifications,
            'unreadNotifications' => $user->unreadNotifications()->count(),
        ]);
    }

    private function pendingReason(Enrollment $e): string
    {
        return match ($e->status) {
            EnrollmentStatus::Pending => 'We’re setting up your access — you’ll get an email as soon as it’s ready.',
            EnrollmentStatus::Partial => 'Complete your payment to unlock full access to this course.',
            EnrollmentStatus::Suspended => 'Access is paused. Contact support to restore it.',
            EnrollmentStatus::Failed => $e->failed_reason ?: 'We hit a snag setting up your access. Our team can help — contact support.',
            EnrollmentStatus::Cancelled => 'This enrolment was cancelled.',
            default => 'Access is not available yet.',
        };
    }

    /**
     * @return array<string, mixed>
     */
    private function eventCard(Event $event): array
    {
        $start = $event->starts_at;
        $end = $event->ends_at;
        $limit = $event->registration_limit;
        $seatsRemaining = $limit !== null ? max(0, $limit - (int) $event->registered_count) : null;
        $minutes = ($start && $end) ? (int) round($start->diffInMinutes($end, true)) : 0;
        $duration = $minutes <= 0 ? null : ($minutes < 60 ? $minutes.' min' : intdiv($minutes, 60).' hr'.(($minutes % 60) ? ' '.($minutes % 60).' min' : ''));

        return [
            'id' => $event->id,
            'title' => $event->title,
            'slug' => $event->slug,
            'summary' => Str::limit(strip_tags((string) $event->description), 120),
            'image' => '/images/consistent.jpg',
            'category' => ['value' => $event->type, 'label' => Str::headline((string) $event->type)],
            'dateLabel' => $start?->translatedFormat('D, M j, Y'),
            'timeLabel' => $start?->translatedFormat('g:i A'),
            'duration' => $duration,
            'deliveryMode' => 'Online',
            'status' => $event->status,
            'seatsRemaining' => $seatsRemaining,
            'isFull' => $seatsRemaining !== null && $seatsRemaining === 0,
            'url' => route('events.show', $event->slug),
            'recordingUrl' => $event->recording_url,
        ];
    }
}
