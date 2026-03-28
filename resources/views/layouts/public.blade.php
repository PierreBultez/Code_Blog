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
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet" media="print" onload="this.media='all'">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL@24,400,0&display=swap" rel="stylesheet" media="print" onload="this.media='all'">
    <noscript>
        <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL@24,400,0&display=swap" rel="stylesheet">
    </noscript>

    {{-- Prevent FOUC for dark mode --}}
    <script>
        if (localStorage.getItem('theme') === 'dark') {
            document.documentElement.classList.add('dark');
        }
    </script>

    @vite(['resources/css/app.css', 'resources/js/public.js'])

    @include('partials.structured-data', [
        'article' => $seoArticle,
        'breadcrumbs' => $seoBreadcrumbs,
    ])
</head>
<body class="bg-background text-on-background font-sans selection:bg-primary-container selection:text-on-primary-container antialiased min-h-screen">
    <x-public.navbar :active="$navActive ?? ''" />

    <main>
        {{ $slot }}
    </main>

    <x-public.footer />
</body>
</html>
