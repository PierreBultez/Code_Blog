<div class="space-y-6">
    <div class="flex justify-between items-center">
        <flux:heading size="xl">Articles</flux:heading>
        <flux:button variant="primary" href="{{ route('dashboard.articles.create') }}" wire:navigate>Create Article</flux:button>
    </div>

    <flux:table>
        <flux:table.columns>
            <flux:table.column>Title</flux:table.column>
            <flux:table.column>Status</flux:table.column>
            <flux:table.column>Date</flux:table.column>
            <flux:table.column>Actions</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @foreach($articles as $article)
                <flux:table.row :key="$article->id">
                    <flux:table.cell>{{ $article->title }}</flux:table.cell>
                    <flux:table.cell>
                        @if($article->is_published)
                            <flux:badge color="green">Published</flux:badge>
                        @else
                            <flux:badge color="zinc">Draft</flux:badge>
                        @endif
                    </flux:table.cell>
                    <flux:table.cell>{{ $article->created_at->format('M d, Y') }}</flux:table.cell>
                    <flux:table.cell>
                        <div class="flex gap-2">
                            <flux:button size="sm" variant="filled" href="{{ route('dashboard.articles.edit', $article) }}" wire:navigate>Edit</flux:button>
                            <flux:button size="sm" variant="danger" wire:click="deleteArticle({{ $article->id }})" wire:confirm="Are you sure you want to delete this article?">Delete</flux:button>
                        </div>
                    </flux:table.cell>
                </flux:table.row>
            @endforeach
        </flux:table.rows>
    </flux:table>

    <div>
        {{ $articles->links() }}
    </div>
</div>
