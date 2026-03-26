@props([
    'article' => null,
    'breadcrumbs' => [],
])

@php
    $appName = config('app.name', '<Code_Blog>');
    $siteUrl = config('app.url');
@endphp

{{-- WebSite Schema (toutes les pages) --}}
<script type="application/ld+json">
{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'WebSite',
    'name' => $appName,
    'url' => $siteUrl,
    'description' => 'Blog technique d\'un développeur web freelance spécialisé Laravel.',
    'inLanguage' => 'fr-FR',
    'author' => [
        '@type' => 'Person',
        'name' => 'Pierre Bultez',
        'url' => 'https://pierrebultez.com',
        'jobTitle' => 'Développeur Web Freelance',
        'sameAs' => [
            'https://github.com/PierreBultez',
            'https://www.linkedin.com/in/pierre-bultez-5699b52a8/',
        ],
    ],
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}
</script>

{{-- BreadcrumbList Schema --}}
@if (count($breadcrumbs) > 0)
<script type="application/ld+json">
{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'BreadcrumbList',
    'itemListElement' => collect($breadcrumbs)->map(fn ($crumb, $index) => [
        '@type' => 'ListItem',
        'position' => $index + 1,
        'name' => $crumb['name'],
        'item' => $crumb['url'],
    ])->values()->all(),
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}
</script>
@endif

{{-- Article Schema --}}
@if ($article)
<script type="application/ld+json">
{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'Article',
    'headline' => $article->title,
    'description' => $article->seo_description,
    'datePublished' => $article->published_at?->toIso8601String(),
    'dateModified' => $article->updated_at?->toIso8601String(),
    'author' => [
        '@type' => 'Person',
        'name' => 'Pierre Bultez',
        'url' => 'https://pierrebultez.com',
    ],
    'publisher' => [
        '@type' => 'Person',
        'name' => 'Pierre Bultez',
    ],
    'mainEntityOfPage' => [
        '@type' => 'WebPage',
        '@id' => route('articles.show', $article),
    ],
    'keywords' => $article->tags->pluck('name')->implode(', '),
    'wordCount' => str_word_count(strip_tags($article->content ?? '')),
    'inLanguage' => 'fr-FR',
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}
</script>
@endif
