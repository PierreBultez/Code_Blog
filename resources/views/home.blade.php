<x-layouts::public :navActive="'home'" :title="'Just another <code_blog>'">

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

        {{-- Bento Grid : Featured Article + Code Snippets --}}
        <div class="grid grid-cols-1 md:grid-cols-12 gap-6 mb-16">
            @if ($featured)
                <article class="md:col-span-8 group relative overflow-hidden bg-white dark:bg-zinc-900 rounded-3xl border border-zinc-100 dark:border-zinc-800 p-8 md:p-12 transition-all hover:shadow-2xl hover:shadow-primary/5 active:scale-[0.99] duration-300">
                    <a href="{{ route('articles.show', $featured) }}" class="absolute inset-0 z-10"></a>
                    <div class="flex flex-col h-full justify-between">
                        <div>
                            <div class="flex items-center gap-4 mb-6">
                                @foreach ($featured->tags as $tag)
                                    <span class="font-mono text-xs uppercase tracking-tighter bg-primary/5 dark:bg-primary/20 text-primary dark:text-red-400 px-3 py-1 rounded-full">
                                        {{ $tag->name }}
                                    </span>
                                @endforeach
                                <span class="font-mono text-xs text-zinc-400">
                                    {{ $featured->published_at?->translatedFormat('d M Y') }}
                                </span>
                            </div>
                            <h2 class="text-3xl md:text-4xl font-extrabold mb-4 group-hover:text-primary dark:group-hover:text-red-400 transition-colors text-zinc-900 dark:text-white">
                                {{ $featured->title }}
                            </h2>
                            <p class="text-zinc-500 dark:text-zinc-400 line-clamp-2 max-w-xl">
                                {{ $featured->excerpt }}
                            </p>
                        </div>
                        <div class="mt-8">
                            <span class="inline-flex items-center gap-2 font-bold text-primary dark:text-red-400">
                                Lire l'article <span class="material-symbols-outlined">arrow_forward</span>
                            </span>
                        </div>
                    </div>
                </article>
            @endif

            <div class="md:col-span-4 flex flex-col gap-6">
                <div class="bg-primary-container text-on-primary-container p-8 rounded-3xl h-full flex flex-col justify-center items-center text-center">
                    <span class="material-symbols-outlined text-4xl mb-6">bar_chart</span>
                    <div class="flex gap-8">
                        <div x-data="{ count: 0, target: {{ $stats['articles'] }} }" x-intersect.once="let start = performance.now(); let duration = 1500; (function step(now) { let progress = Math.min((now - start) / duration, 1); $data.count = Math.floor(progress * $data.target); if (progress < 1) requestAnimationFrame(step); })(start);">
                            <p class="text-4xl font-black tabular-nums" x-text="count">0</p>
                            <p class="text-sm opacity-80 mt-1">articles</p>
                        </div>
                        <div x-data="{ count: 0, target: {{ $stats['tags'] }} }" x-intersect.once="let start = performance.now(); let duration = 1500; (function step(now) { let progress = Math.min((now - start) / duration, 1); $data.count = Math.floor(progress * $data.target); if (progress < 1) requestAnimationFrame(step); })(start);">
                            <p class="text-4xl font-black tabular-nums" x-text="count">0</p>
                            <p class="text-sm opacity-80 mt-1">sujets</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Articles récents --}}
        @if ($recentArticles->isNotEmpty())
            <section class="space-y-6">
                <div class="flex items-center justify-between mb-8 border-b border-zinc-100 dark:border-zinc-800 pb-4">
                    <h3 class="text-xl font-bold font-mono uppercase tracking-widest text-zinc-400">Articles Récents</h3>
                </div>

                @foreach ($recentArticles as $index => $article)
                    <article class="group relative">
                        <a href="{{ route('articles.show', $article) }}" class="absolute inset-0 z-10"></a>
                        <div class="flex flex-col md:flex-row md:items-center justify-between p-6 rounded-2xl hover:bg-white dark:hover:bg-zinc-900 transition-all border border-transparent hover:border-zinc-100 dark:hover:border-zinc-800">
                            <div class="flex items-center gap-6">
                                <span class="hidden md:block font-mono text-zinc-300 dark:text-zinc-600 text-lg">{{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}</span>
                                <div>
                                    <div class="flex items-center gap-3 mb-1">
                                        @foreach ($article->tags as $tag)
                                            <span class="font-mono text-[10px] uppercase tracking-widest text-primary dark:text-red-400">{{ $tag->name }}</span>
                                        @endforeach
                                        <span class="font-mono text-[10px] text-zinc-400">{{ $article->published_at?->translatedFormat('d M Y') }}</span>
                                    </div>
                                    <h4 class="text-xl font-bold group-hover:translate-x-1 transition-transform text-zinc-900 dark:text-white">{{ $article->title }}</h4>
                                    @if ($article->excerpt)
                                        <p class="text-sm text-zinc-500 dark:text-zinc-400 line-clamp-1 mt-1 max-w-xl">{{ $article->excerpt }}</p>
                                    @endif
                                </div>
                            </div>
                            <div class="mt-4 md:mt-0 opacity-0 group-hover:opacity-100 transition-opacity">
                                <span class="material-symbols-outlined text-primary dark:text-red-400">trending_flat</span>
                            </div>
                        </div>
                    </article>
                @endforeach
            </section>
        @endif
    </section>

</x-layouts::public>
