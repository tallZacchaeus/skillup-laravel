<x-filament-widgets::widget>
    <div class="skillup-ahero">
        <div class="skillup-ahero__intro">
            <p class="skillup-ahero__eyebrow">{{ $greeting }}@if ($name), {{ $name }}@endif</p>
            <h2 class="skillup-ahero__title">Operations command centre</h2>
            <p class="skillup-ahero__lede">Your control centre for the SkillUp platform. Press
                <kbd class="skillup-ahero__kbd">⌘</kbd><kbd class="skillup-ahero__kbd">K</kbd>
                to search across orders, courses, cohorts, programmes and support.</p>
        </div>

        <div class="skillup-ahero__security" role="group" aria-label="Session context">
            <p class="skillup-ahero__security-label">Session</p>
            <ul class="skillup-ahero__security-list">
                <li>
                    <span class="skillup-ahero__meta-label">Environment</span>
                    <span class="skillup-ahero__badge skillup-ahero__badge--{{ $isProduction ? 'prod' : 'nonprod' }}">{{ ucfirst($environment) }}</span>
                </li>
                @if ($roles)
                    <li>
                        <span class="skillup-ahero__meta-label">Role</span>
                        <span class="skillup-ahero__meta-value">{{ $roles }}</span>
                    </li>
                @endif
            </ul>
        </div>
    </div>
</x-filament-widgets::widget>
