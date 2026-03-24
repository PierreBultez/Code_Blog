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
