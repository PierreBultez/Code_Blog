@props(['active' => ''])

<nav x-data="{ open: false }" class="sticky top-0 z-50 flex justify-between items-center px-6 md:px-8 py-3 max-w-5xl mx-auto bg-white/70 dark:bg-zinc-900/70 backdrop-blur-md rounded-full mt-4 border border-white/20 dark:border-zinc-800/50 shadow-xl shadow-black/5 antialiased">
    <a href="{{ route('home') }}" class="text-2xl font-black tracking-tighter text-zinc-900 dark:text-white">&lt;Code_Blog&gt;</a>

    {{-- Desktop links --}}
    <div class="hidden md:flex items-center gap-8">
        <a href="{{ route('home') }}"
           class="{{ $active === 'home' ? 'text-primary dark:text-red-400 font-bold border-b-2 border-primary dark:border-red-400 pb-1' : 'text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-zinc-100 transition-colors' }}">
            Accueil
        </a>
        <a href="{{ route('articles.index') }}"
           class="{{ $active === 'articles' ? 'text-primary dark:text-red-400 font-bold border-b-2 border-primary dark:border-red-400 pb-1' : 'text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-zinc-100 transition-colors' }}">
            Articles
        </a>
        <a href="{{ route('about') }}"
           class="{{ $active === 'about' ? 'text-primary dark:text-red-400 font-bold border-b-2 border-primary dark:border-red-400 pb-1' : 'text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-zinc-100 transition-colors' }}">
            &Agrave; Propos
        </a>
    </div>

    <div class="flex items-center gap-2">
        {{-- Dark mode toggle --}}
        <button
            x-data="{ dark: localStorage.getItem('theme') === 'dark' }"
            x-init="$watch('dark', val => { localStorage.setItem('theme', val ? 'dark' : 'light'); document.documentElement.classList.toggle('dark', val) }); document.documentElement.classList.toggle('dark', dark)"
            x-on:click="dark = !dark"
            class="p-2 hover:bg-zinc-100/50 dark:hover:bg-zinc-800/50 rounded-full transition-all active:scale-95 duration-200 ease-in-out"
        >
            <span x-show="!dark" class="material-symbols-outlined text-zinc-600 dark:text-zinc-400">dark_mode</span>
            <span x-show="dark" x-cloak class="material-symbols-outlined text-zinc-600 dark:text-zinc-400">light_mode</span>
        </button>

        {{-- Mobile menu toggle --}}
        <button x-on:click="open = !open" class="md:hidden p-2 hover:bg-zinc-100/50 dark:hover:bg-zinc-800/50 rounded-full transition-all">
            <span x-show="!open" class="material-symbols-outlined text-zinc-600 dark:text-zinc-400">menu</span>
            <span x-show="open" x-cloak class="material-symbols-outlined text-zinc-600 dark:text-zinc-400">close</span>
        </button>
    </div>

    {{-- Mobile menu --}}
    <div x-show="open" x-cloak x-transition class="absolute top-full left-0 right-0 mt-2 mx-4 p-4 bg-white/90 dark:bg-zinc-900/90 backdrop-blur-md rounded-2xl border border-white/20 dark:border-zinc-800/50 shadow-xl md:hidden">
        <div class="flex flex-col gap-3">
            <a href="{{ route('home') }}" class="{{ $active === 'home' ? 'text-primary font-bold' : 'text-zinc-600 dark:text-zinc-400' }} px-4 py-2 rounded-lg hover:bg-zinc-100/50 dark:hover:bg-zinc-800/50 transition-colors">Accueil</a>
            <a href="{{ route('articles.index') }}" class="{{ $active === 'articles' ? 'text-primary font-bold' : 'text-zinc-600 dark:text-zinc-400' }} px-4 py-2 rounded-lg hover:bg-zinc-100/50 dark:hover:bg-zinc-800/50 transition-colors">Articles</a>
            <a href="{{ route('about') }}" class="{{ $active === 'about' ? 'text-primary font-bold' : 'text-zinc-600 dark:text-zinc-400' }} px-4 py-2 rounded-lg hover:bg-zinc-100/50 dark:hover:bg-zinc-800/50 transition-colors">&Agrave; Propos</a>
        </div>
    </div>
</nav>
