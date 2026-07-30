<x-filament-widgets::widget>
    <div class="skillup-chero">
        <div class="skillup-chero__intro">
            <p class="skillup-chero__eyebrow">{{ $greeting }}@if ($contactName), {{ $contactName }}@endif</p>
            @if ($orgName)
                <h2 class="skillup-chero__org">{{ $orgName }}</h2>
            @endif
            <p class="skillup-chero__lede">Here’s where your organisation’s learning stands today.</p>

            <div class="skillup-chero__actions">
                <a href="{{ $inviteUrl }}" class="skillup-chero__btn skillup-chero__btn--primary">Invite team members</a>
                <a href="{{ $billingUrl }}" class="skillup-chero__btn skillup-chero__btn--ghost">View billing</a>
            </div>
        </div>

        <div class="skillup-chero__alerts" role="group" aria-label="Priority alerts">
            <p class="skillup-chero__alerts-label">Needs your attention</p>
            @forelse ($alerts as $alert)
                @php $tag = $alert['url'] ? 'a' : 'div'; @endphp
                <{{ $tag }} @if ($alert['url']) href="{{ $alert['url'] }}" @endif class="skillup-chero__alert skillup-chero__alert--{{ $alert['tone'] }}">
                    <span class="skillup-chero__alert-dot" aria-hidden="true"></span>
                    <span>{{ $alert['label'] }}</span>
                </{{ $tag }}>
            @empty
                <div class="skillup-chero__alert skillup-chero__alert--ok">
                    <span class="skillup-chero__alert-dot" aria-hidden="true"></span>
                    <span>You’re all caught up — nothing needs attention.</span>
                </div>
            @endforelse
        </div>
    </div>
</x-filament-widgets::widget>
