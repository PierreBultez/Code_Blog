<x-layouts::public
    :navActive="'about'"
    :title="'À Propos — Pierre Bultez, Développeur Laravel Freelance'"
    :seoDescription="'Pierre Bultez, développeur web freelance spécialisé Laravel, basé dans le Vaucluse. Laravel, Livewire, Tailwind CSS, VueJS — disponible en remote.'"
    :seoBreadcrumbs="[
        ['name' => 'Accueil', 'url' => route('home')],
        ['name' => 'À Propos', 'url' => route('about')],
    ]"
>

    <section class="pt-6 pb-20 px-6 max-w-5xl mx-auto">
        {{-- Hero --}}
        <div class="mb-20">
            <h1 class="font-headline font-extrabold text-5xl md:text-7xl tracking-tighter text-primary mb-6">Salut, moi c'est Pierre.</h1>
            <p class="text-xl text-on-surface-variant leading-relaxed">Développeur web freelance. Je construis des applications avec Laravel et son écosystème, et j'écris ici pour garder une trace de ce que j'apprends au quotidien.</p>
        </div>

        {{-- Bento Grid --}}
        <div class="grid grid-cols-12 gap-6 mb-32">
            {{-- Profile Card --}}
            <div class="col-span-12 md:col-span-4 bg-surface/40 backdrop-blur-sm border border-outline-variant/30 p-8 rounded-xl flex flex-col justify-between">
                <div>
                    <div class="w-24 h-24 rounded-full mb-6 overflow-hidden border-2 border-primary/20">
                        <img src="{{ asset('images/photo-profil.avif') }}" alt="Pierre — Photo de profil" class="w-full h-full object-cover" />
                    </div>
                    <h2 class="font-headline font-bold text-2xl mb-2 text-on-surface">Pierre</h2>
                    <p class="text-primary font-mono text-sm mb-4">pierre.bultez@proton.me:~#</p>
                    <p class="text-on-surface-variant leading-relaxed">Freelance spécialisé Laravel. Ce blog me sert de mémoire technique — et si ça peut aider d'autres devs au passage, tant mieux.</p>
                </div>
                <div class="flex gap-4 mt-8" aria-hidden="true">
                    <span class="material-symbols-outlined text-primary">terminal</span>
                    <span class="material-symbols-outlined text-primary">code</span>
                    <span class="material-symbols-outlined text-primary">database</span>
                </div>
            </div>

            {{-- Skills Terminal --}}
            <div class="col-span-12 md:col-span-8 bg-zinc-900 rounded-xl p-6 font-mono text-sm text-zinc-300 shadow-2xl relative overflow-hidden">
                <div class="flex gap-2 mb-4">
                    <div class="w-3 h-3 rounded-full bg-red-500"></div>
                    <div class="w-3 h-3 rounded-full bg-amber-500"></div>
                    <div class="w-3 h-3 rounded-full bg-emerald-500"></div>
                </div>
                <div class="space-y-2">
                    <p><span class="text-emerald-400">➜</span> <span class="text-secondary">skills</span> list --category development</p>
                    <p class="pl-4 opacity-80">Laravel, Livewire, VueJS, NuxtJS, Tailwind CSS</p>
                    <p><span class="text-emerald-400">➜</span> <span class="text-secondary">skills</span> list --category devops-and-tools</p>
                    <p class="pl-4 opacity-80">Ubuntu, Bash, Redis, Github Actions, Jetbrains</p>
                    <p><span class="text-emerald-400">➜</span> <span class="text-secondary">status</span> check --system</p>
                    <p class="pl-4 text-red-400 animate-pulse">● System Operational - Uptime: 99.9%</p>
                </div>
                <div class="absolute right-10 bottom-10 opacity-30" aria-hidden="true">
                    <span class="material-symbols-outlined text-[200px]">settings_ethernet</span>
                </div>
            </div>

            {{-- Philosophie --}}
            <div class="col-span-12 lg:col-span-7 bg-primary-container text-on-primary-container p-8 rounded-xl flex flex-col justify-center">
                <h2 class="font-headline font-bold text-3xl mb-4">Pourquoi ce blog</h2>
                <p class="text-lg opacity-90 leading-relaxed">Plutôt que de redemander la même chose à une IA ou de fouiller dans mes anciens projets, j'écris ici ce qui fonctionne. Des notes claires, des exemples concrets, pour moi d'abord — et pour ceux que ça intéresse.</p>
            </div>

            {{-- Social Links --}}
            <div class="col-span-12 lg:col-span-5 grid grid-cols-2 gap-4">
                <a href="https://github.com/PierreBultez" target="_blank" rel="noopener noreferrer" class="bg-surface/40 backdrop-blur-sm border border-outline-variant/30 p-6 rounded-xl flex items-center justify-center hover:bg-primary/5 transition-all">
                    <div class="text-center">
                        <span class="material-symbols-outlined text-primary text-3xl mb-2" aria-hidden="true">commit</span>
                        <p class="font-bold text-on-surface">GitHub</p>
                    </div>
                </a>
                <a href="https://www.linkedin.com/in/pierre-bultez-5699b52a8/" target="_blank" rel="noopener noreferrer" class="bg-surface/40 backdrop-blur-sm border border-outline-variant/30 p-6 rounded-xl flex items-center justify-center hover:bg-primary/5 transition-all">
                    <div class="text-center">
                        <span class="material-symbols-outlined text-primary text-3xl mb-2" aria-hidden="true">add_reaction</span>
                        <p class="font-bold text-on-surface">LinkedIn</p>
                    </div>
                </a>
                <a href="https://pierrebultez.com" target="_blank" rel="noopener noreferrer" class="bg-surface/40 backdrop-blur-sm border border-outline-variant/30 p-6 rounded-xl flex items-center justify-center hover:bg-primary/5 transition-all">
                    <div class="text-center">
                        <span class="material-symbols-outlined text-primary text-3xl mb-2" aria-hidden="true">alternate_email</span>
                        <p class="font-bold text-on-surface">Web</p>
                    </div>
                </a>
                <a href="https://cv.pierrebultez.com" target="_blank" rel="noopener noreferrer" class="bg-surface/40 backdrop-blur-sm border border-outline-variant/30 p-6 rounded-xl flex items-center justify-center hover:bg-primary/5 transition-all">
                    <div class="text-center">
                        <span class="material-symbols-outlined text-primary text-3xl mb-2" aria-hidden="true">demography</span>
                        <p class="font-bold text-on-surface">CV</p>
                    </div>
                </a>
            </div>
        </div>

        {{-- Contact Section --}}
        <section class="grid grid-cols-1 lg:grid-cols-2 gap-16 items-start" aria-labelledby="contact-heading">
            <div class="space-y-8">
                <div>
                    <h2 id="contact-heading" class="font-headline font-extrabold text-4xl text-on-surface mb-4">Un mot à me dire ?</h2>
                    <p class="text-on-surface-variant text-lg">Une question sur un article, une suggestion, ou juste envie d'échanger — n'hésite pas.</p>
                </div>
                <div class="space-y-6">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-lg bg-primary/10 flex items-center justify-center text-primary">
                            <span class="material-symbols-outlined" aria-hidden="true">mail</span>
                        </div>
                        <div>
                            <p class="text-xs text-on-surface-variant uppercase tracking-widest font-bold">Email</p>
                            <p class="text-lg font-medium text-on-surface">pierre.bultez@proton.me</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-lg bg-primary/10 flex items-center justify-center text-primary">
                            <span class="material-symbols-outlined" aria-hidden="true">location_on</span>
                        </div>
                        <div>
                            <p class="text-xs text-on-surface-variant uppercase tracking-widest font-bold">Localisation</p>
                            <p class="text-lg font-medium text-on-surface">Vaucluse, France (Remote OK)</p>
                        </div>
                    </div>
                </div>
            </div>

            <livewire:contact-form />
        </section>
    </section>

</x-layouts::public>
