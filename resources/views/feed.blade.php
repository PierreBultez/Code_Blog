{!! '<?xml version="1.0" encoding="UTF-8"?>' !!}
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
    <channel>
        <title>{{ config('app.name') }}</title>
        <description>Blog technique d'un développeur web freelance Laravel. Tutoriels, retours d'expérience et bonnes pratiques.</description>
        <link>{{ route('home') }}</link>
        <language>fr</language>
        <lastBuildDate>{{ $articles->first()?->published_at?->toRfc2822String() ?? now()->toRfc2822String() }}</lastBuildDate>
        <atom:link href="{{ route('feed') }}" rel="self" type="application/rss+xml" />
        @foreach ($articles as $article)
            <item>
                <title>{{ htmlspecialchars($article->title, ENT_XML1, 'UTF-8') }}</title>
                <link>{{ route('articles.show', $article->slug) }}</link>
                <guid isPermaLink="true">{{ route('articles.show', $article->slug) }}</guid>
                <pubDate>{{ $article->published_at->toRfc2822String() }}</pubDate>
                <description>{{ htmlspecialchars($article->excerpt ?? '', ENT_XML1, 'UTF-8') }}</description>
            </item>
        @endforeach
    </channel>
</rss>
