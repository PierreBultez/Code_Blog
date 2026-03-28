<x-layouts::public
    :navActive="'home'"
    :title="'Blog Développeur Laravel Freelance'"
    :seoDescription="'Carnet de bord technique d\'un développeur web freelance spécialisé Laravel. Tutoriels, retours d\'expérience et bonnes pratiques PHP, Livewire, Tailwind CSS.'"
>

    <section class="max-w-5xl mx-auto px-6 md:px-8 pt-20 md:pt-32">
        {{-- Hero --}}
        <div class="mb-24">
            <h1 class="text-5xl md:text-8xl font-black tracking-tight mb-8 leading-[1.1] text-zinc-900 dark:text-white">
                Notes d'un <span class="text-primary-container dark:text-red-400">dev</span> en freelance
            </h1>
            <p class="text-xl md:text-2xl text-zinc-500 dark:text-zinc-400 max-w-2xl font-light leading-relaxed">
                Un carnet de bord technique. J'y note ce que j'apprends, les solutions que je trouve, et les bonnes pratiques que je ne veux pas oublier.
            </p>
        </div>

        {{-- Bento Grid : 2 Featured Articles + Stats --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-16">
            @foreach ($featuredArticles as $featured)
                <article class="group relative overflow-hidden bg-white dark:bg-zinc-900 rounded-3xl border border-zinc-100 dark:border-zinc-800 transition-all hover:shadow-2xl hover:shadow-primary/5 active:scale-[0.99] duration-300 flex flex-col">
                    <a href="{{ route('articles.show', $featured) }}" aria-label="{{ $featured->title }}" class="absolute inset-0 z-10 focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-2 rounded-3xl"></a>
                    <img src="{{ $featured->og_image_url }}" alt="{{ $featured->title }}" width="1200" height="630" class="w-full aspect-[1.91/1] object-cover" loading="eager">
                    <div class="flex flex-col flex-1 p-6">
                        <div class="flex items-center gap-3 mb-3">
                            @foreach ($featured->tags as $tag)
                                <span class="font-mono text-xs uppercase tracking-tighter bg-primary/5 dark:bg-primary/20 text-primary dark:text-red-400 px-2 py-0.5 rounded-full">
                                    {{ $tag->name }}
                                </span>
                            @endforeach
                            <span class="font-mono text-xs text-on-surface-variant">
                                {{ $featured->published_at?->translatedFormat('d M Y') }}
                            </span>
                        </div>
                        <h2 class="text-xl font-extrabold mb-2 group-hover:text-primary dark:group-hover:text-red-400 transition-colors text-zinc-900 dark:text-white line-clamp-2">
                            {{ $featured->title }}
                        </h2>
                        <p class="text-sm text-zinc-500 dark:text-zinc-400 line-clamp-2 flex-1">
                            {{ $featured->excerpt }}
                        </p>
                        <div class="mt-4">
                            <span class="inline-flex items-center gap-1 text-sm font-bold text-primary dark:text-red-400">
                                Lire <span class="material-symbols-outlined text-base" aria-hidden="true">arrow_forward</span>
                            </span>
                        </div>
                    </div>
                </article>
            @endforeach

            <div class="flex flex-col gap-6">
                <div class="bg-primary-container text-on-primary-container p-8 rounded-3xl h-full flex flex-col justify-center items-center text-center">
                    <span class="material-symbols-outlined text-4xl mb-6" aria-hidden="true">bar_chart</span>
                    <div class="flex gap-8">
                        <div x-data="{ count: 0, target: {{ $stats['articles'] }} }" x-intersect.once="if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) { $data.count = $data.target; return; } let start = performance.now(); let duration = 1500; (function step(now) { let progress = Math.min((now - start) / duration, 1); $data.count = Math.floor(progress * $data.target); if (progress < 1) requestAnimationFrame(step); })(start);">
                            <p class="text-4xl font-black tabular-nums" x-text="count" aria-hidden="true">0</p>
                            <span class="sr-only">{{ $stats['articles'] }}</span>
                            <p class="text-sm opacity-80 mt-1">articles</p>
                        </div>
                        <div x-data="{ count: 0, target: {{ $stats['tags'] }} }" x-intersect.once="if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) { $data.count = $data.target; return; } let start = performance.now(); let duration = 1500; (function step(now) { let progress = Math.min((now - start) / duration, 1); $data.count = Math.floor(progress * $data.target); if (progress < 1) requestAnimationFrame(step); })(start);">
                            <p class="text-4xl font-black tabular-nums" x-text="count" aria-hidden="true">0</p>
                            <span class="sr-only">{{ $stats['tags'] }}</span>
                            <p class="text-sm opacity-80 mt-1">sujets</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Articles récents --}}
        @if ($recentArticles->isNotEmpty())
            <section class="space-y-6" aria-labelledby="recent-articles-heading">
                <div class="flex items-center justify-between mb-8 border-b border-zinc-100 dark:border-zinc-800 pb-4">
                    <h2 id="recent-articles-heading" class="text-xl font-bold font-mono uppercase tracking-widest text-on-surface-variant">Articles Récents</h2>
                </div>

                @foreach ($recentArticles as $index => $article)
                    <article class="group relative">
                        <a href="{{ route('articles.show', $article) }}" aria-label="{{ $article->title }}" class="absolute inset-0 z-10 focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-2 rounded-2xl"></a>
                        <div class="flex flex-col md:flex-row md:items-center gap-6 p-6 rounded-2xl hover:bg-white dark:hover:bg-zinc-900 transition-all border border-transparent hover:border-zinc-100 dark:hover:border-zinc-800">
                            <img src="{{ $article->og_image_url }}" alt="{{ $article->title }}" width="1200" height="630" class="w-full md:w-40 lg:w-48 shrink-0 aspect-[1.91/1] object-cover rounded-lg" loading="lazy">
                            <div class="flex-1">
                                <div class="flex items-center gap-3 mb-1">
                                    @foreach ($article->tags as $tag)
                                        <span class="font-mono text-xs uppercase tracking-widest text-primary dark:text-red-400">{{ $tag->name }}</span>
                                    @endforeach
                                    <span class="font-mono text-xs text-on-surface-variant">{{ $article->published_at?->translatedFormat('d M Y') }}</span>
                                </div>
                                <h3 class="text-xl font-bold group-hover:translate-x-1 transition-transform text-zinc-900 dark:text-white">{{ $article->title }}</h3>
                                @if ($article->excerpt)
                                    <p class="text-sm text-zinc-500 dark:text-zinc-400 line-clamp-1 mt-1 max-w-xl">{{ $article->excerpt }}</p>
                                @endif
                            </div>
                        </div>
                    </article>
                @endforeach
            </section>
        @endif
    </section>

</x-layouts::public>
