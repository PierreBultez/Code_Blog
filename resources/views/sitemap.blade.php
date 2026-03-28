{!! '<?xml version="1.0" encoding="UTF-8"?>' !!}
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <url>
        <loc>{{ route('home') }}</loc>
        <lastmod>{{ $articles->first()?->updated_at?->toW3cString() ?? now()->toW3cString() }}</lastmod>
    </url>
    <url>
        <loc>{{ route('articles.index') }}</loc>
        <lastmod>{{ $articles->first()?->published_at?->toW3cString() ?? now()->toW3cString() }}</lastmod>
    </url>
    <url>
        <loc>{{ route('about') }}</loc>
    </url>
    @foreach ($articles as $article)
        <url>
            <loc>{{ route('articles.show', $article->slug) }}</loc>
            <lastmod>{{ $article->updated_at->toW3cString() }}</lastmod>
        </url>
    @endforeach
</urlset>
