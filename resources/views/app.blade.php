<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title inertia>{{ config('app.name', 'SkillUp') }}</title>
        {{-- description + Open Graph / Twitter defaults are Inertia-managed (see PublicLayout) so pages can override them per route. --}}
        <meta name="theme-color" content="#0D4EFF">

        <!-- Favicons / PWA -->
        <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
        <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
        <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">
        <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">
        <link rel="manifest" href="{{ asset('site.webmanifest') }}">

        <!-- Structured data (Organization + WebSite) -->
        @php
            $orgLd = [
                '@context' => 'https://schema.org',
                '@type' => 'Organization',
                'name' => config('app.name', 'SkillUp'),
                'url' => url('/'),
                'logo' => asset('images/skillUp.png'),
                'sameAs' => [
                    'https://www.facebook.com/skillupedtech',
                    'https://youtube.com/@theskillupedtech',
                    'https://www.linkedin.com/company/theskillupglobal',
                ],
                'contactPoint' => [
                    '@type' => 'ContactPoint',
                    'contactType' => 'customer support',
                    'email' => 'skilluplimited@gmail.com',
                ],
            ];
            $siteLd = [
                '@context' => 'https://schema.org',
                '@type' => 'WebSite',
                'name' => config('app.name', 'SkillUp'),
                'url' => url('/'),
                'potentialAction' => [
                    '@type' => 'SearchAction',
                    'target' => [
                        '@type' => 'EntryPoint',
                        'urlTemplate' => url('/courses').'?search={search_term_string}',
                    ],
                    'query-input' => 'required name=search_term_string',
                ],
            ];
        @endphp
        <script type="application/ld+json">{!! json_encode($orgLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
        <script type="application/ld+json">{!! json_encode($siteLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

        <!-- Scripts -->
        @routes
        @viteReactRefresh
        @vite('resources/js/app.jsx')
        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        @inertia
    </body>
</html>
