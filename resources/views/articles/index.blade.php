<x-layouts::public
    :navActive="'articles'"
    :title="'Articles & Tutoriels Laravel'"
    :seoDescription="'Tous les articles du blog : tutoriels Laravel, Livewire, Tailwind CSS, déploiement, bonnes pratiques PHP et retours d\'expérience de développeur freelance.'"
    :seoBreadcrumbs="[
        ['name' => 'Accueil', 'url' => route('home')],
        ['name' => 'Articles', 'url' => route('articles.index')],
    ]"
>

    <section class="max-w-4xl mx-auto px-6 pt-20 pb-12">
        {{-- Header --}}
        <header class="mb-16">
            <h1 class="text-5xl md:text-6xl font-extrabold tracking-tight text-on-surface mb-4">Manuscrits</h1>
            <p class="text-on-surface-variant text-lg max-w-xl">Tout ce que je note au fil de mes projets : des retours d'expérience, des astuces, et des choses que j'aurais aimé savoir plus tôt.</p>
        </header>

        {{-- Tag Filters --}}
        @if ($tags->isNotEmpty())
            <div class="flex flex-wrap gap-3 mb-12">
                <a href="{{ route('articles.index') }}"
                   class="font-mono text-xs uppercase tracking-wider px-4 py-2 rounded-full border transition-all {{ !$activeTag ? 'bg-primary text-on-primary border-primary' : 'border-outline-variant text-on-surface-variant hover:border-primary hover:text-primary' }}">
                    Tous
                </a>
                @foreach ($tags as $tag)
                    <a href="{{ route('articles.index', ['tag' => $tag->slug]) }}"
                       class="font-mono text-xs uppercase tracking-wider px-4 py-2 rounded-full border transition-all {{ $activeTag === $tag->slug ? 'bg-primary text-on-primary border-primary' : 'border-outline-variant text-on-surface-variant hover:border-primary hover:text-primary' }}">
                        {{ $tag->name }}
                    </a>
                @endforeach
            </div>
        @endif

        {{-- Articles List --}}
        <div class="space-y-6">
            @forelse ($articles as $article)
                <article class="group relative p-8 rounded-xl border border-outline-variant/30 bg-surface/40 hover:bg-surface/80 backdrop-blur-sm transition-all duration-300 hover:shadow-2xl hover:shadow-primary/5 active:scale-[0.99]">
                    <a href="{{ route('articles.show', $article) }}" class="absolute inset-0 z-10"></a>
                    <div class="flex flex-col md:flex-row md:items-start gap-6">
                        <div class="md:w-48 lg:w-56 shrink-0">
                            <img
                                src="{{ $article->og_image_url }}"
                                alt="{{ $article->title }}"
                                class="w-full aspect-[1.91/1] object-cover rounded-lg"
                                loading="lazy"
                            >
                        </div>
                        <div class="flex-1">
                            @if ($article->tags->isNotEmpty())
                                <div class="flex flex-wrap gap-2 mb-3">
                                    @foreach ($article->tags as $tag)
                                        <span class="font-mono text-xs font-medium uppercase tracking-wider text-primary">
                                            {{ $tag->name }}
                                        </span>
                                    @endforeach
                                </div>
                            @endif
                            <h2 class="text-2xl font-bold text-on-surface group-hover:text-primary transition-colors mb-2">
                                {{ $article->title }}
                            </h2>
                            <p class="text-on-surface-variant line-clamp-2 mb-4">
                                {{ $article->excerpt }}
                            </p>
                            <div class="flex items-center gap-4 text-sm text-outline">
                                <span>{{ $article->published_at?->translatedFormat('d M Y') }}</span>
                                <span class="w-1 h-1 bg-outline-variant rounded-full"></span>
                                <span>{{ $article->reading_time }} min de lecture</span>
                            </div>
                        </div>
                    </div>
                </article>
            @empty
                <p class="text-on-surface-variant text-center py-12">Aucun article publié pour le moment.</p>
            @endforelse
        </div>

        {{-- Pagination --}}
        @if ($articles->hasPages())
            <div class="mt-16">
                {{ $articles->links() }}
            </div>
        @endif
    </section>

</x-layouts::public>
