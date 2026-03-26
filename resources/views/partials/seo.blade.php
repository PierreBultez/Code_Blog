@props([
    'title' => config('app.name', '<Code_Blog>'),
    'description' => 'Blog technique d\'un développeur web freelance Laravel. Tutoriels, retours d\'expérience et bonnes pratiques.',
    'canonical' => null,
    'ogType' => 'website',
    'ogImage' => null,
    'article' => null,
])

@php
    $pageTitle = $title;
    $pageDescription = $description;
    $canonicalUrl = $canonical ?? url()->current();
    $ogImageUrl = $ogImage ?? asset('images/og-default.png');

    if ($article) {
        $pageDescription = $article->seo_description ?: $pageDescription;
    }
@endphp

{{-- Meta Description --}}
<meta name="description" content="{{ Str::limit($pageDescription, 160, '') }}">

{{-- Canonical URL --}}
<link rel="canonical" href="{{ $canonicalUrl }}">

{{-- Open Graph --}}
<meta property="og:type" content="{{ $ogType }}">
<meta property="og:title" content="{{ $pageTitle }}">
<meta property="og:description" content="{{ Str::limit($pageDescription, 160, '') }}">
<meta property="og:url" content="{{ $canonicalUrl }}">
<meta property="og:image" content="{{ $ogImageUrl }}">
<meta property="og:locale" content="fr_FR">
<meta property="og:site_name" content="{{ config('app.name', '<Code_Blog>') }}">

@if ($article)
    <meta property="article:published_time" content="{{ $article->published_at?->toIso8601String() }}">
    <meta property="article:modified_time" content="{{ $article->updated_at?->toIso8601String() }}">
    <meta property="article:author" content="Pierre Bultez">
    @foreach ($article->tags as $tag)
        <meta property="article:tag" content="{{ $tag->name }}">
    @endforeach
@endif

{{-- Twitter Card --}}
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $pageTitle }}">
<meta name="twitter:description" content="{{ Str::limit($pageDescription, 160, '') }}">
<meta name="twitter:image" content="{{ $ogImageUrl }}">
