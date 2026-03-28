<div class="bg-surface-container border border-outline-variant p-8 md:p-10 rounded-xl shadow-xl">
    @if ($sent)
        <div class="text-center py-8 space-y-4">
            <span class="material-symbols-outlined text-emerald-500 text-5xl" aria-hidden="true">check_circle</span>
            <h3 class="font-headline font-bold text-2xl text-on-surface">Message envoyé !</h3>
            <p class="text-on-surface-variant">Merci pour votre message. Vous recevrez une confirmation par email.</p>
            <button
                wire:click="$set('sent', false)"
                class="mt-4 text-primary hover:text-primary-container transition-colors font-medium"
            >
                Envoyer un autre message
            </button>
        </div>
    @else
        <form wire:submit="send" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-2">
                    <label for="name" class="text-sm font-bold text-on-surface-variant ml-1">Nom Complet</label>
                    <input
                        wire:model="name"
                        id="name"
                        type="text"
                        placeholder="Jean Dupont"
                        aria-describedby="error-contact-name"
                        class="w-full bg-background border border-outline-variant rounded-lg p-3 focus:ring-2 focus:ring-primary focus:border-transparent outline-none transition-all text-on-surface"
                    />
                    @error('name') <p id="error-contact-name" role="alert" class="text-red-500 text-sm ml-1">{{ $message }}</p> @enderror
                </div>
                <div class="space-y-2">
                    <label for="subject" class="text-sm font-bold text-on-surface-variant ml-1">Sujet</label>
                    <select
                        wire:model="subject"
                        id="subject"
                        aria-describedby="error-contact-subject"
                        class="w-full bg-background border border-outline-variant rounded-lg p-3 focus:ring-2 focus:ring-primary focus:border-transparent outline-none transition-all text-on-surface"
                    >
                        <option value="" disabled selected>Choisir un sujet...</option>
                        <option>Développement</option>
                        <option>Infrastructure / SysAdmin</option>
                        <option>Autre</option>
                    </select>
                    @error('subject') <p id="error-contact-subject" role="alert" class="text-red-500 text-sm ml-1">{{ $message }}</p> @enderror
                </div>
            </div>
            <div class="space-y-2">
                <label for="email" class="text-sm font-bold text-on-surface-variant ml-1">Email</label>
                <input
                    wire:model="email"
                    id="email"
                    type="email"
                    placeholder="jean@example.com"
                    aria-describedby="error-contact-email"
                    class="w-full bg-background border border-outline-variant rounded-lg p-3 focus:ring-2 focus:ring-primary focus:border-transparent outline-none transition-all text-on-surface"
                />
                @error('email') <p id="error-contact-email" role="alert" class="text-red-500 text-sm ml-1">{{ $message }}</p> @enderror
            </div>
            <div class="space-y-2">
                <label for="message" class="text-sm font-bold text-on-surface-variant ml-1">Message</label>
                <textarea
                    wire:model="message"
                    id="message"
                    placeholder="Votre message ici..."
                    rows="4"
                    aria-describedby="error-contact-message"
                    class="w-full bg-background border border-outline-variant rounded-lg p-3 focus:ring-2 focus:ring-primary focus:border-transparent outline-none transition-all resize-none text-on-surface"
                ></textarea>
                @error('message') <p id="error-contact-message" role="alert" class="text-red-500 text-sm ml-1">{{ $message }}</p> @enderror
            </div>
            <button
                type="submit"
                class="w-full bg-primary text-on-primary py-4 rounded-lg font-bold text-lg hover:bg-primary-container transition-all flex items-center justify-center gap-2 group"
                wire:loading.attr="disabled"
                wire:loading.class="opacity-50 cursor-not-allowed"
            >
                <span wire:loading.remove>Envoyer le message</span>
                <span wire:loading>Envoi en cours...</span>
                <span class="material-symbols-outlined group-hover:translate-x-1 transition-transform" wire:loading.remove aria-hidden="true">send</span>
                <span class="material-symbols-outlined animate-spin" wire:loading aria-hidden="true">progress_activity</span>
            </button>
        </form>
    @endif
</div>
