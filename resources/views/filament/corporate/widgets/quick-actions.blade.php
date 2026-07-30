<x-filament-widgets::widget>
    <section aria-labelledby="corp-qa-heading">
        <h3 id="corp-qa-heading" class="skillup-cqa__heading">Quick actions</h3>
        <div class="skillup-cqa__grid">
            @foreach ($actions as $action)
                <a href="{{ $action['url'] }}" class="skillup-cqa__card">
                    <span class="skillup-cqa__icon">
                        <x-filament::icon :icon="$action['icon']" class="h-6 w-6" />
                    </span>
                    <span class="skillup-cqa__body">
                        <span class="skillup-cqa__label">{{ $action['label'] }}</span>
                        <span class="skillup-cqa__desc">{{ $action['description'] }}</span>
                    </span>
                    <x-filament::icon icon="heroicon-m-arrow-right" class="skillup-cqa__arrow h-5 w-5" />
                </a>
            @endforeach
        </div>
    </section>
</x-filament-widgets::widget>
