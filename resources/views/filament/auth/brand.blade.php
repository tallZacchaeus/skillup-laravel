{{-- Reusable branded panel for Filament auth pages (login + password recovery),
     shared by the instructor and corporate portals. Variant-driven so each
     portal keeps a distinct look. Purely promotional → hidden from assistive
     tech; the auth card carries the real logo, headings, and controls. Shown
     only on large screens.

     Data (passed from the panel render hook):
       $variant     'instructor' | 'corporate'
       $markSuffix  wordmark suffix, e.g. "Corporate"
       $eyebrow, $headline, $lede, $secure  strings
       $bullets     array<string>
--}}
<aside class="skillup-auth-brand skillup-auth-brand--{{ $variant }}" aria-hidden="true">
    <div class="skillup-auth-brand__inner">
        <div class="skillup-auth-brand__mark">
            SkillUp <span>{{ $markSuffix }}</span>
        </div>

        <div class="skillup-auth-brand__body">
            <p class="skillup-auth-brand__eyebrow">{{ $eyebrow }}</p>
            <h2 class="skillup-auth-brand__headline">{{ $headline }}</h2>
            <p class="skillup-auth-brand__lede">{{ $lede }}</p>
            <ul class="skillup-auth-brand__list">
                @foreach ($bullets as $bullet)
                    <li>{{ $bullet }}</li>
                @endforeach
            </ul>
        </div>

        <p class="skillup-auth-brand__secure">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect width="18" height="11" x="3" y="11" rx="2" ry="2" /><path d="M7 11V7a5 5 0 0 1 10 0v4" />
            </svg>
            {{ $secure }}
        </p>
    </div>
</aside>
