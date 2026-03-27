<section class="mt-20 border-t border-outline-variant pt-16 pb-12">
    <h3 class="text-2xl font-bold font-headline mb-8 flex items-center gap-3 text-on-surface">
        <span class="material-symbols-outlined text-primary">chat_bubble</span>
        Discussion
        @if ($comments->count())
            <span class="text-base font-normal text-on-surface-variant">({{ $comments->count() }})</span>
        @endif
    </h3>

    {{-- Liste des commentaires --}}
    @if ($comments->isNotEmpty())
        <div class="space-y-6 mb-12">
            @foreach ($comments as $comment)
                <div class="bg-surface-container border border-outline-variant rounded-xl p-6" wire:key="comment-{{ $comment->id }}">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-9 h-9 rounded-full bg-primary/10 flex items-center justify-center">
                            <span class="text-primary font-bold text-sm">{{ strtoupper(mb_substr($comment->author_name, 0, 1)) }}</span>
                        </div>
                        <div>
                            <p class="font-bold text-on-surface text-sm">{{ $comment->author_name }}</p>
                            <p class="text-xs text-outline">{{ $comment->created_at->diffForHumans() }}</p>
                        </div>
                    </div>
                    <div class="text-on-surface-variant leading-relaxed whitespace-pre-line">{!! preg_replace('/@(\w+)/', '<span class="text-primary font-semibold">@$1</span>', e($comment->content)) !!}</div>
                </div>
            @endforeach
        </div>
    @endif

    {{-- Formulaire --}}
    @if ($sent)
        <div class="bg-surface-container border border-outline-variant rounded-xl p-8 text-center space-y-3">
            <span class="material-symbols-outlined text-emerald-500 text-4xl">check_circle</span>
            <p class="text-on-surface font-bold">Commentaire publié !</p>
            <button
                wire:click="$set('sent', false)"
                class="text-primary hover:text-primary-container transition-colors font-medium text-sm"
            >
                Écrire un autre commentaire
            </button>
        </div>
    @else
        <form wire:submit="addComment" class="bg-surface-container border border-outline-variant rounded-xl p-6 md:p-8 space-y-5">
            <h4 class="font-bold text-on-surface">Laisser un commentaire</h4>

            <div class="space-y-2">
                <label for="author_name" class="text-sm font-bold text-on-surface-variant ml-1">Pseudo</label>
                <input
                    wire:model="author_name"
                    id="author_name"
                    type="text"
                    placeholder="Votre pseudo"
                    maxlength="50"
                    class="w-full max-w-xs bg-background border border-outline-variant rounded-lg p-3 focus:ring-2 focus:ring-primary focus:border-transparent outline-none transition-all text-on-surface"
                />
                @error('author_name') <p class="text-red-500 text-sm ml-1">{{ $message }}</p> @enderror
            </div>

            <div class="space-y-2">
                <label for="comment_content" class="text-sm font-bold text-on-surface-variant ml-1">Commentaire</label>
                <textarea
                    wire:model="content"
                    id="comment_content"
                    placeholder="Votre commentaire... Utilisez @pseudo pour mentionner quelqu'un."
                    rows="4"
                    maxlength="2000"
                    class="w-full bg-background border border-outline-variant rounded-lg p-3 focus:ring-2 focus:ring-primary focus:border-transparent outline-none transition-all resize-none text-on-surface"
                ></textarea>
                @error('content') <p class="text-red-500 text-sm ml-1">{{ $message }}</p> @enderror
            </div>

            {{-- Honeypot --}}
            <div class="hidden" aria-hidden="true">
                <input wire:model="honeypot" type="text" tabindex="-1" autocomplete="off" />
            </div>

            <button
                type="submit"
                class="bg-primary text-on-primary px-6 py-3 rounded-lg font-bold hover:bg-primary-container transition-all flex items-center gap-2 group"
                wire:loading.attr="disabled"
                wire:loading.class="opacity-50 cursor-not-allowed"
            >
                <span wire:loading.remove>Publier</span>
                <span wire:loading>Publication...</span>
                <span class="material-symbols-outlined text-lg" wire:loading.remove>send</span>
                <span class="material-symbols-outlined text-lg animate-spin" wire:loading>progress_activity</span>
            </button>
        </form>
    @endif
</section>
