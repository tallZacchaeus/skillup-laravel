{{-- Reusable security reassurance + support link shown beneath a Filament auth
     form. Shared by the instructor and corporate portals.

     Data:
       $variant      'instructor' | 'corporate'  (drives the accent colour)
       $secure       reassurance sentence
       $supportUrl   href for the support link
       $supportLabel link text
--}}
<div class="skillup-auth-note skillup-auth-note--{{ $variant }}">
    <p class="skillup-auth-note__secure">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <rect width="18" height="11" x="3" y="11" rx="2" ry="2" /><path d="M7 11V7a5 5 0 0 1 10 0v4" />
        </svg>
        <span>{{ $secure }}</span>
    </p>
    <p class="skillup-auth-note__support">
        Trouble signing in?
        <a href="{{ $supportUrl }}">{{ $supportLabel }}</a>
    </p>
</div>
