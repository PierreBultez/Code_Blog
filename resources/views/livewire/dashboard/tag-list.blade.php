<div class="space-y-6">
    <div class="flex justify-between items-center">
        <flux:heading size="xl">Tags</flux:heading>
        <flux:button variant="primary" href="{{ route('dashboard.tags.create') }}" wire:navigate>Create Tag</flux:button>
    </div>

    <div class="max-w-4xl mx-auto">
        <flux:table>
            <flux:table.columns>
                <flux:table.column>Name</flux:table.column>
                <flux:table.column>Slug</flux:table.column>
                <flux:table.column>Actions</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @foreach($tags as $tag)
                    <flux:table.row :key="$tag->id">
                        <flux:table.cell>{{ $tag->name }}</flux:table.cell>
                        <flux:table.cell>{{ $tag->slug }}</flux:table.cell>
                        <flux:table.cell>
                            <div class="flex gap-2">
                                <flux:button size="sm" variant="filled" href="{{ route('dashboard.tags.edit', $tag) }}" wire:navigate>Edit</flux:button>
                                <flux:button size="sm" variant="danger" wire:click="deleteTag({{ $tag->id }})" wire:confirm="Are you sure you want to delete this tag?">Delete</flux:button>
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @endforeach
            </flux:table.rows>
        </flux:table>
    </div>

    <div>
        {{ $tags->links() }}
    </div>
</div>
