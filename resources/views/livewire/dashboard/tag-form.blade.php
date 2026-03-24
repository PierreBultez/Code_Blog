<div class="max-w-2xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <flux:heading size="xl">{{ $tag ? 'Edit Tag' : 'Create Tag' }}</flux:heading>
    </div>

    <form wire:submit="save" class="space-y-6">
        <flux:field>
            <flux:label>Name</flux:label>
            <flux:input wire:model="name" />
            <flux:error name="name" />
        </flux:field>

        <div class="flex justify-end gap-4">
            <flux:button variant="filled" href="{{ route('dashboard.tags.index') }}" wire:navigate>Cancel</flux:button>
            <flux:button type="submit" variant="primary">Save Tag</flux:button>
        </div>
    </form>
</div>
