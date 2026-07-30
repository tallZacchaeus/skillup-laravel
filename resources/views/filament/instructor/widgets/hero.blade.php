<x-filament-widgets::widget>
    <div class="skillup-ihero">
        <div class="skillup-ihero__intro">
            <p class="skillup-ihero__eyebrow">{{ $greeting }}</p>
            <h2 class="skillup-ihero__name">{{ $name }}</h2>

            <p class="skillup-ihero__priorities">
                @if ($sessionsToday > 0)
                    You have <strong>{{ $sessionsToday }}</strong> {{ \Illuminate\Support\Str::plural('session', $sessionsToday) }} to teach today.
                @else
                    No sessions scheduled today — a good time to prep or write up notes.
                @endif
                @if ($notesPending > 0)
                    <span class="skillup-ihero__flag">{{ $notesPending }} {{ \Illuminate\Support\Str::plural('session', $notesPending) }} awaiting notes</span>
                @endif
            </p>
        </div>

        @if ($nextSession)
            <div class="skillup-ihero__next" role="group" aria-label="Next teaching session">
                <p class="skillup-ihero__next-label">Next session</p>
                <p class="skillup-ihero__next-title">{{ $nextSession->title }}</p>
                <p class="skillup-ihero__next-meta">
                    {{ $nextSession->cohort?->title }} · {{ $nextSession->starts_at->format('D, M j · g:i A') }}
                    <span class="skillup-ihero__countdown">({{ $nextSession->starts_at->diffForHumans() }})</span>
                </p>
                <div class="skillup-ihero__actions">
                    @if ($nextSession->meeting_url)
                        <a href="{{ $nextSession->meeting_url }}" target="_blank" rel="noopener" class="skillup-ihero__join">
                            Join session
                        </a>
                    @endif
                    <a href="{{ $sessionsUrl }}" class="skillup-ihero__all">View all sessions</a>
                </div>
            </div>
        @else
            <div class="skillup-ihero__next skillup-ihero__next--empty">
                <p class="skillup-ihero__next-label">Next session</p>
                <p class="skillup-ihero__next-meta">No upcoming sessions scheduled.</p>
                <a href="{{ $sessionsUrl }}" class="skillup-ihero__all">View sessions</a>
            </div>
        @endif
    </div>
</x-filament-widgets::widget>
