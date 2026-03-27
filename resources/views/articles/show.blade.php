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

    <article class="max-w-4xl mx-auto px-6 mt-20">
        {{-- Header --}}
        <header class="mb-12">
            <div class="flex items-center gap-4 mb-6 text-sm font-medium tracking-wide">
                @foreach ($article->tags as $tag)
                    <span class="bg-primary/10 text-primary px-3 py-1 rounded-full uppercase">
                        {{ $tag->name }}
                    </span>
                @endforeach
                <time class="text-outline" datetime="{{ $article->published_at?->toDateString() }}">
                    {{ $article->published_at?->translatedFormat('d M Y') }}
                </time>
            </div>
            <h1 class="text-4xl md:text-6xl font-extrabold font-headline tracking-tight text-on-surface mb-8 leading-[1.1]">
                {{ $article->title }}
            </h1>
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-full bg-surface-container-high flex items-center justify-center">
                    <span class="material-symbols-outlined text-primary">person</span>
                </div>
                <div>
                    <p class="font-bold text-on-surface">Pierre</p>
                    <p class="text-xs text-outline font-medium">{{ $article->reading_time }} min de lecture</p>
                </div>
            </div>
        </header>

        {{-- Content --}}
        <section class="prose prose-zinc dark:prose-invert lg:prose-xl max-w-none text-on-surface-variant leading-relaxed">
            {!! $article->content !!}
        </section>

        {{-- Discussion --}}
        <livewire:article-comments :article="$article" />
    </article>

</x-layouts::public>
