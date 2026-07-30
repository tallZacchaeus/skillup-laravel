<x-filament-widgets::widget>
    <section aria-labelledby="admin-qa-heading">
        <h3 id="admin-qa-heading" class="skillup-aqa__heading">Quick actions</h3>
        <div class="skillup-aqa__grid">
            @foreach ($actions as $action)
                <a href="{{ $action['url'] }}" class="skillup-aqa__card">
                    <span class="skillup-aqa__icon">
                        <x-filament::icon :icon="$action['icon']" class="h-5 w-5" />
                    </span>
                    <span class="skillup-aqa__label">{{ $action['label'] }}</span>
                </a>
            @endforeach
        </div>
    </section>
</x-filament-widgets::widget>
