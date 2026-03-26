<div class="max-w-6xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <flux:heading size="xl">{{ $article ? 'Edit Article' : 'Create Article' }}</flux:heading>
    </div>

    <form wire:submit="save" class="space-y-6">
        <flux:field>
            <flux:label>Title</flux:label>
            <flux:input wire:model="title" />
            <flux:error name="title" />
        </flux:field>

        <flux:field>
            <flux:label>Excerpt</flux:label>
            <flux:textarea wire:model="excerpt" rows="3" />
            <flux:error name="excerpt" />
        </flux:field>

        <flux:field>
            <flux:label>Meta Description (SEO)</flux:label>
            <flux:textarea wire:model="meta_description" rows="2" placeholder="Description pour les moteurs de recherche (max 160 caractères). Laissez vide pour utiliser l'extrait." />
            <flux:description>{{ Str::length($meta_description ?? '') }}/160 caractères</flux:description>
            <flux:error name="meta_description" />
        </flux:field>

        <flux:field>
            <flux:label>Image OG (SEO & illustration)</flux:label>
            <flux:description>Format recommandé : 1200x630px, PNG ou JPG (max 2 Mo). Laissez vide pour une image générée automatiquement.</flux:description>

            @if ($og_image_upload)
                <div class="mt-2 relative inline-block">
                    <img src="{{ $og_image_upload->temporaryUrl() }}" alt="Aperçu image OG" class="rounded-lg border border-zinc-200 dark:border-zinc-700 max-w-md">
                    <flux:button type="button" variant="danger" size="xs" wire:click="$set('og_image_upload', null)" class="absolute top-2 right-2">Retirer</flux:button>
                </div>
            @elseif ($article?->og_image && !$remove_og_image)
                <div class="mt-2 relative inline-block">
                    <img src="{{ asset('storage/' . $article->og_image) }}" alt="Image OG actuelle" class="rounded-lg border border-zinc-200 dark:border-zinc-700 max-w-md">
                    <flux:button type="button" variant="danger" size="xs" wire:click="removeOgImage" class="absolute top-2 right-2">Supprimer</flux:button>
                </div>
            @endif

            <flux:input type="file" wire:model="og_image_upload" accept="image/png,image/jpeg,image/webp" />
            <flux:error name="og_image_upload" />
        </flux:field>

        <flux:field>
            <flux:label>Texte miniature</flux:label>
            <flux:input wire:model="og_text" placeholder="Texte court pour la miniature générée. Laissez vide pour utiliser le titre." />
            <flux:description>Texte affiché sur la miniature auto-générée (max 100 car.). Idéal pour raccourcir un titre trop long.</flux:description>
            <flux:error name="og_text" />
        </flux:field>

        <flux:field>
            <flux:label>Content</flux:label>
            <x-tiny-mce wire:model="content" />
            <flux:error name="content" />
        </flux:field>

        <flux:field>
            <flux:label>Tags</flux:label>
            <div class="flex flex-wrap gap-4 mt-2">
                @foreach($availableTags as $tag)
                    <flux:checkbox wire:model="selectedTags" value="{{ $tag->id }}" label="{{ $tag->name }}" />
                @endforeach
            </div>
            <flux:error name="selectedTags" />

            <div class="mt-4 flex items-end gap-4 max-w-sm">
                <flux:field class="flex-1">
                    <flux:label class="sr-only">Add New Tag</flux:label>
                    <flux:input wire:model="newTag" placeholder="New tag name..." />
                </flux:field>
                <flux:button type="button" variant="outline" wire:click="addTag">Add Tag</flux:button>
            </div>
            <flux:error name="newTag" />
        </flux:field>

        <flux:field>
            <flux:switch wire:model="is_published" label="Publish Article" description="Make this article visible to the public." />
            <flux:error name="is_published" />
        </flux:field>

        <div class="flex justify-end gap-4">
            <flux:button variant="filled" href="{{ route('dashboard.articles.index') }}" wire:navigate>Cancel</flux:button>
            <flux:button type="submit" variant="primary">Save Article</flux:button>
        </div>
    </form>
</div>
