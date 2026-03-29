@props([
    'title' => null,
    'navActive' => '',
    'seoDescription' => null,
    'seoCanonical' => null,
    'seoOgType' => 'website',
    'seoOgImage' => null,
    'seoArticle' => null,
    'seoBreadcrumbs' => [],
])

@php
    $appName = config('app.name', '<Code_Blog>');
    $pageTitle = $title ? $title . ' — ' . $appName : $appName . ' — Blog dev Laravel freelance';
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $pageTitle }}</title>

    @include('partials.seo', [
        'title' => $pageTitle,
        'description' => $seoDescription,
        'canonical' => $seoCanonical,
        'ogType' => $seoOgType,
        'ogImage' => $seoOgImage,
        'article' => $seoArticle,
    ])

    {{-- RSS feed discovery --}}
    <link rel="alternate" type="application/rss+xml" title="{{ $appName }}" href="{{ route('feed') }}">

    {{-- Fonts (non-render-blocking) --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preload" as="style" href="https://fonts.googleapis.com/css2?family=Manrope:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap">
    <link rel="preload" as="style" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL@24,400,0&display=swap">
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet" media="print" data-async-font>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL@24,400,0&display=swap" rel="stylesheet" media="print" data-async-font>
    <noscript>
        <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL@24,400,0&display=swap" rel="stylesheet">
    </noscript>

    {{-- Prevent FOUC for dark mode --}}
    <script nonce="{{ Vite::cspNonce() }}">
        if (localStorage.getItem('theme') === 'dark') {
            document.documentElement.classList.add('dark');
        }
    </script>

    @vite(['resources/css/public.css', 'resources/js/public.js'])

    @include('partials.structured-data', [
        'article' => $seoArticle,
        'breadcrumbs' => $seoBreadcrumbs,
    ])
</head>
<body class="bg-background text-on-background font-sans selection:bg-primary-container selection:text-on-primary-container antialiased min-h-screen">
    <a href="#main-content" class="sr-only focus:not-sr-only focus:absolute focus:top-4 focus:left-4 focus:z-[100] focus:bg-primary focus:text-on-primary focus:px-4 focus:py-2 focus:rounded-lg focus:font-bold focus:shadow-lg">
        Aller au contenu
    </a>

    <x-public.navbar :active="$navActive ?? ''" />

    @if (count($seoBreadcrumbs) > 1)
        <nav aria-label="Fil d'Ariane" class="max-w-4xl mx-auto px-6 pt-20 pb-0">
            <ol class="flex flex-wrap items-center gap-1 text-sm text-on-surface-variant">
                @foreach ($seoBreadcrumbs as $i => $crumb)
                    @if ($i > 0)
                        <li aria-hidden="true" class="select-none">/</li>
                    @endif

                    @if ($i === count($seoBreadcrumbs) - 1)
                        <li aria-current="page" class="text-on-surface font-medium truncate max-w-xs">{{ $crumb['name'] }}</li>
                    @else
                        <li><a href="{{ $crumb['url'] }}" class="hover:text-on-surface transition-colors">{{ $crumb['name'] }}</a></li>
                    @endif
                @endforeach
            </ol>
        </nav>
    @endif

    <main id="main-content">
        {{ $slot }}
    </main>

    <x-public.footer />
</body>
</html>
