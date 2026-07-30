<x-filament-widgets::widget>
    <section aria-labelledby="quick-actions-heading">
        <h3 id="quick-actions-heading" class="skillup-iqa__heading">Quick actions</h3>
        <div class="skillup-iqa__grid">
            @foreach ($actions as $action)
                <a href="{{ $action['url'] }}" class="skillup-iqa__card">
                    <span class="skillup-iqa__icon">
                        <x-filament::icon :icon="$action['icon']" class="h-6 w-6" />
                    </span>
                    <span class="skillup-iqa__body">
                        <span class="skillup-iqa__label">{{ $action['label'] }}</span>
                        <span class="skillup-iqa__desc">{{ $action['description'] }}</span>
                    </span>
                    <x-filament::icon icon="heroicon-m-arrow-right" class="skillup-iqa__arrow h-5 w-5" />
                </a>
            @endforeach
        </div>
    </section>
</x-filament-widgets::widget>
