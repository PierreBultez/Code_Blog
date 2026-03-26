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

    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">

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
