<?php

namespace App\Http\Controllers;

use App\Enums\ProgramEditionStatus;
use App\Models\Programs\Program;
use App\Models\Programs\ProgramEdition;
use App\Models\Programs\ProgramEditionTrack;
use App\Services\Discounts\DiscountEligibilityService;
use Inertia\Inertia;
use Inertia\Response;

class ProgramController extends Controller
{
    public function index(): Response
    {
        $programs = Program::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->map(fn (Program $program) => $this->programCard($program))
            ->filter(fn (?array $card) => $card !== null)
            ->values();

        // Facets are derived from the programs that actually exist, so a chip
        // never appears unless at least one real programme matches it.
        $statusFacets = collect(self::STATUS_FILTERS)
            ->filter(fn (array $f) => $programs->contains(fn ($p) => $p['statusKey'] === $f['key']))
            ->values();

        $tagFacets = $programs
            ->flatMap(fn ($p) => $p['tags'])
            ->unique()
            ->sort()
            ->map(fn (string $tag) => ['key' => \Illuminate\Support\Str::slug($tag), 'label' => $tag, 'type' => 'tag'])
            ->values();

        // The flagship: an explicitly-featured programme, else the first one
        // that is open for registration. Null when neither exists.
        $featuredSlug = $programs->firstWhere('featured', true)['slug']
            ?? $programs->firstWhere('statusKey', 'open')['slug']
            ?? null;

        return Inertia::render('Public/Programs/Index', [
            'programs' => $programs,
            'featuredSlug' => $featuredSlug,
            'statusFilters' => $statusFacets,
            'tagFilters' => $tagFacets,
        ]);
    }

    /** Human labels + filter keys for each real edition status. */
    private const STATUS_FILTERS = [
        ['key' => 'open', 'label' => 'Open', 'type' => 'status'],
        ['key' => 'coming-soon', 'label' => 'Coming soon', 'type' => 'status'],
        ['key' => 'in-progress', 'label' => 'In progress', 'type' => 'status'],
        ['key' => 'completed', 'label' => 'Completed', 'type' => 'status'],
        ['key' => 'closed', 'label' => 'Closed', 'type' => 'status'],
    ];

    /**
     * A rich, real-data card for one programme. Returns null when the
     * programme has no publicly-visible edition to show.
     *
     * @return array<string, mixed>|null
     */
    private function programCard(Program $program): ?array
    {
        $edition = $program->currentEdition();

        if (! $edition) {
            return null;
        }

        $edition->loadMissing('tracks');

        $ages = $edition->tracks
            ->flatMap(fn (ProgramEditionTrack $t) => [$t->age_min, $t->age_max])
            ->filter(fn ($v) => $v !== null);
        $audience = $ages->isNotEmpty() ? 'Ages '.$ages->min().'–'.$ages->max() : null;

        $durationWeeks = ($edition->starts_on && $edition->ends_on)
            ? max(1, (int) ceil($edition->starts_on->diffInDays($edition->ends_on) / 7))
            : null;

        [$statusKey, $statusLabel] = $this->statusFor($edition);

        $meta = $program->metadata ?? [];

        return [
            'slug' => $program->slug,
            'name' => $program->name,
            'tagline' => $program->tagline,
            'description' => $program->description,
            'featured' => (bool) ($meta['featured'] ?? false),
            'tags' => array_values(array_filter((array) ($meta['tags'] ?? []))),
            'statusKey' => $statusKey,
            'statusLabel' => $statusLabel,
            'acceptsRegistrations' => $edition->status->acceptsRegistrations(),
            'editionTitle' => $edition->title,
            'year' => $edition->year,
            'startsOn' => $edition->starts_on?->toDateString(),
            'endsOn' => $edition->ends_on?->toDateString(),
            'scheduleText' => $edition->schedule_text,
            'durationWeeks' => $durationWeeks,
            'deliveryMode' => $edition->delivery_mode,
            'venueName' => $edition->venue_name,
            'audience' => $audience,
            'heroImagePath' => $edition->hero_image_path,
        ];
    }

    /**
     * Maps a real edition status to a public filter key + badge label.
     * "Closing soon" only when a genuine registration deadline is near.
     *
     * @return array{0: string, 1: string}
     */
    private function statusFor(ProgramEdition $edition): array
    {
        $deadline = $edition->metadata['registration_deadline'] ?? null;

        return match ($edition->status) {
            ProgramEditionStatus::RegistrationOpen => ($deadline && \Illuminate\Support\Carbon::parse($deadline)->isBetween(now(), now()->addDays(7)))
                ? ['open', 'Closing soon']
                : ['open', 'Open'],
            ProgramEditionStatus::Announced => ['coming-soon', 'Coming soon'],
            ProgramEditionStatus::SoldOut => ['open', 'Full'],
            ProgramEditionStatus::Running => ['in-progress', 'In progress'],
            ProgramEditionStatus::Completed => ['completed', 'Completed'],
            ProgramEditionStatus::Archived => ['closed', 'Closed'],
            default => ['coming-soon', 'Coming soon'],
        };
    }

    public function show(Program $program): Response
    {
        abort_unless($program->is_active, 404);

        $edition = $program->currentEdition();

        abort_unless($edition, 404);

        return $this->renderEdition($program, $edition);
    }

    public function showEdition(Program $program, string $editionSlug): Response
    {
        $edition = $program->editions()
            ->where('slug', $editionSlug)
            ->whereIn('status', ProgramEditionStatus::publicValues())
            ->firstOrFail();

        return $this->renderEdition($program, $edition);
    }

    private function renderEdition(Program $program, ProgramEdition $edition): Response
    {
        $edition->load(['tracks.product.defaultPrice']);

        $discounts = app(DiscountEligibilityService::class);

        $tracks = $edition->tracks->map(function (ProgramEditionTrack $track) use ($discounts) {
            $price = $track->product?->defaultPrice;
            $amount = $price ? (float) $price->amount : null;
            $discounted = null;

            if ($track->product && $amount !== null) {
                $result = $discounts->validate('preview@skillup.internal', $track->product, $amount);

                if ($result->valid && $result->discountAmount > 0) {
                    $discounted = max(0, $amount - $result->discountAmount);
                }
            }

            return [
                'id' => $track->id,
                'name' => $track->name,
                'slug' => $track->slug,
                'ageMin' => $track->age_min,
                'ageMax' => $track->age_max,
                'summary' => $track->summary,
                'curriculum' => $track->curriculum,
                'capacity' => $track->capacity,
                'seatsRemaining' => $track->seatsRemaining(),
                'isFull' => $track->isFull(),
                'currency' => $price?->currency,
                'amount' => $amount,
                'discountedAmount' => $discounted,
            ];
        });

        // Edition-wide seat picture, derived from real per-track capacity. Null
        // whenever any track has open (uncapped) capacity so we never imply a
        // hard number that isn't true.
        $trackSeats = $edition->tracks->map(fn (ProgramEditionTrack $t) => $t->seatsRemaining());
        $seatsRemaining = $trackSeats->contains(null) ? null : (int) $trackSeats->sum();
        $seatsTotal = (int) $edition->tracks->sum('capacity') ?: $edition->capacity_total;

        $meta = $edition->metadata ?? [];

        return Inertia::render('Public/Programs/Show', [
            'program' => [
                'slug' => $program->slug,
                'name' => $program->name,
                'tagline' => $program->tagline,
            ],
            'edition' => [
                'slug' => $edition->slug,
                'year' => $edition->year,
                'title' => $edition->title,
                'theme' => $edition->theme,
                'status' => $edition->status->value,
                'acceptsRegistrations' => $edition->status->acceptsRegistrations(),
                'startsOn' => $edition->starts_on?->toDateString(),
                'endsOn' => $edition->ends_on?->toDateString(),
                'scheduleText' => $edition->schedule_text,
                'venueName' => $edition->venue_name,
                'venueAddress' => $edition->venue_address,
                'venueMapUrl' => $edition->venue_map_url,
                'deliveryMode' => $edition->delivery_mode,
                'ageReferenceDate' => $edition->ageReferenceDate()->toDateString(),
                'capacityTotal' => $edition->capacity_total,
                'seatsRemaining' => $seatsRemaining,
                'seatsTotal' => $seatsTotal,
                'allowInstallments' => (bool) $edition->allow_installments,
                'paymentMode' => $edition->payment_mode,
                'termsUrl' => $edition->terms_url,
                // Optional real dates for urgency UI — null (and therefore hidden)
                // unless an admin has set them in the edition metadata.
                'registrationDeadline' => $meta['registration_deadline'] ?? null,
                'earlyBirdEndsOn' => $meta['early_bird_ends_on'] ?? null,
                'content' => $edition->content ?? [],
                'contactWhatsapp' => $edition->contact_whatsapp,
                'contactEmail' => $edition->contact_email,
                'heroImagePath' => $edition->hero_image_path,
                'seoTitle' => $edition->seo_title ?? $edition->title,
                'seoDescription' => $edition->seo_description,
            ],
            'tracks' => $tracks,
            'archiveEditions' => $program->editions()
                ->whereKeyNot($edition->id)
                ->whereIn('status', [ProgramEditionStatus::Completed->value, ProgramEditionStatus::Archived->value])
                ->get(['slug', 'year', 'title'])
                ->map(fn ($past) => ['slug' => $past->slug, 'year' => $past->year, 'title' => $past->title]),
        ]);
    }

    /**
     * @return array<string, mixed>|null
     */
    private function editionSummary(?ProgramEdition $edition): ?array
    {
        if (! $edition) {
            return null;
        }

        return [
            'slug' => $edition->slug,
            'year' => $edition->year,
            'title' => $edition->title,
            'status' => $edition->status->value,
            'startsOn' => $edition->starts_on?->toDateString(),
            'venueName' => $edition->venue_name,
        ];
    }
}
