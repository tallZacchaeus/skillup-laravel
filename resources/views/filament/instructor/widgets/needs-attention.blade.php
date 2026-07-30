<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            <span class="skillup-ina__heading">
                <x-filament::icon icon="heroicon-m-flag" class="h-5 w-5" />
                Needs attention
            </span>
        </x-slot>

        <ul class="skillup-ina__list" role="list">
            @foreach ($notesPending as $session)
                <li class="skillup-ina__item">
                    <span class="skillup-ina__dot skillup-ina__dot--danger" aria-hidden="true"></span>
                    <span class="skillup-ina__text">
                        Add notes for <strong>{{ $session->title }}</strong>
                        <span class="skillup-ina__muted">{{ $session->cohort?->title }} · ended {{ $session->ends_at->diffForHumans() }}</span>
                    </span>
                    <a href="{{ $sessionsUrl }}" class="skillup-ina__action">Add notes</a>
                </li>
            @endforeach

            @foreach ($cohortsWithoutUpcoming as $cohort)
                <li class="skillup-ina__item">
                    <span class="skillup-ina__dot skillup-ina__dot--warning" aria-hidden="true"></span>
                    <span class="skillup-ina__text">
                        No upcoming session for <strong>{{ $cohort->title }}</strong>
                        <span class="skillup-ina__muted">Schedule the next session to keep learners on track</span>
                    </span>
                    <a href="{{ $cohortsUrl }}" class="skillup-ina__action">View cohort</a>
                </li>
            @endforeach
        </ul>
    </x-filament::section>
</x-filament-widgets::widget>
