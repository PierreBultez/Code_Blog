<x-layouts::public
    :navActive="'articles'"
    :title="$article->title"
    :seoDescription="$article->seo_description"
    :seoOgType="'article'"
    :seoOgImage="$article->og_image_url"
    :seoArticle="$article"
    :seoBreadcrumbs="[
        ['name' => 'Accueil', 'url' => route('home')],
        ['name' => 'Articles', 'url' => route('articles.index')],
        ['name' => $article->title, 'url' => route('articles.show', $article)],
    ]"
>

    <article class="max-w-4xl mx-auto px-6 mt-6">
        {{-- Header --}}
        <header class="mb-12">
            <div class="flex items-center gap-4 mb-6 text-sm font-medium tracking-wide">
                @foreach ($article->tags as $tag)
                    <span class="bg-primary/10 text-primary px-3 py-1 rounded-full uppercase">
                        {{ $tag->name }}
                    </span>
                @endforeach
                <time class="text-on-surface-variant" datetime="{{ $article->published_at?->toDateString() }}">
                    {{ $article->published_at?->translatedFormat('d M Y') }}
                </time>
            </div>
            <h1 class="text-4xl md:text-6xl font-extrabold font-headline tracking-tight text-on-surface mb-8 leading-[1.1]">
                {{ $article->title }}
            </h1>
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-full bg-surface-container-high flex items-center justify-center">
                    <span class="material-symbols-outlined text-primary" aria-hidden="true">person</span>
                </div>
                <div>
                    <p class="font-bold text-on-surface">Pierre</p>
                    <p class="text-xs text-on-surface-variant font-medium">{{ $article->reading_time }} min de lecture</p>
                </div>
            </div>
        </header>

        {{-- Content --}}
        <section class="prose prose-zinc dark:prose-invert lg:prose-xl max-w-none text-on-surface-variant leading-relaxed">
            {!! $article->content !!}
        </section>

        {{-- Articles connexes --}}
        @if ($relatedArticles->isNotEmpty())
            <section class="mt-16 pt-12 border-t border-outline-variant" aria-labelledby="related-articles-heading">
                <h2 id="related-articles-heading" class="text-2xl font-bold text-on-surface mb-8">Articles connexes</h2>
                <div class="grid gap-6 md:grid-cols-3">
                    @foreach ($relatedArticles as $related)
                        <a href="{{ route('articles.show', $related) }}" class="group block">
                            <img src="{{ $related->og_image_url }}" alt="{{ $related->title }}" width="1200" height="630" class="w-full aspect-[1.91/1] object-cover rounded-lg mb-3" loading="lazy">
                            <div class="flex items-center gap-2 mb-2 text-xs text-on-surface-variant">
                                @foreach ($related->tags->take(2) as $tag)
                                    <span class="bg-primary/10 text-primary px-2 py-0.5 rounded-full uppercase font-medium">{{ $tag->name }}</span>
                                @endforeach
                                <time datetime="{{ $related->published_at?->toDateString() }}">{{ $related->published_at?->translatedFormat('d M Y') }}</time>
                            </div>
                            <h3 class="font-bold text-on-surface group-hover:text-primary transition-colors line-clamp-2">{{ $related->title }}</h3>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif

        {{-- Discussion --}}
        <livewire:article-comments :article="$article" />
    </article>

</x-layouts::public>
