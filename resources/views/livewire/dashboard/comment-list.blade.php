<div class="space-y-6">
    <div class="flex justify-between items-center">
        <flux:heading size="xl">Commentaires</flux:heading>
        @if ($unreadCount > 0)
            <flux:button variant="filled" wire:click="markAllAsRead">
                Tout marquer comme lu ({{ $unreadCount }})
            </flux:button>
        @endif
    </div>

    <flux:table>
        <flux:table.columns>
            <flux:table.column>Auteur</flux:table.column>
            <flux:table.column>Article</flux:table.column>
            <flux:table.column>Commentaire</flux:table.column>
            <flux:table.column>Date</flux:table.column>
            <flux:table.column>Actions</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse($comments as $comment)
                <flux:table.row :key="$comment->id">
                    <flux:table.cell>
                        <span class="font-medium">{{ $comment->author_name }}</span>
                    </flux:table.cell>
                    <flux:table.cell>
                        @if ($comment->article)
                            <a href="{{ route('articles.show', $comment->article) }}" target="_blank" class="text-sm hover:underline">
                                {{ Str::limit($comment->article->title, 30) }}
                            </a>
                        @else
                            <span class="text-zinc-400 italic">Article supprimé</span>
                        @endif
                    </flux:table.cell>
                    <flux:table.cell>
                        <span class="text-sm">{{ Str::limit($comment->content, 60) }}</span>
                    </flux:table.cell>
                    <flux:table.cell>
                        <span class="text-sm">{{ $comment->created_at->diffForHumans() }}</span>
                    </flux:table.cell>
                    <flux:table.cell>
                        <flux:button size="sm" variant="danger" wire:click="deleteComment({{ $comment->id }})" wire:confirm="Êtes-vous sûr de vouloir supprimer ce commentaire ?">Supprimer</flux:button>
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="5" class="text-center text-zinc-400">
                        Aucun commentaire pour le moment.
                    </flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>

    <div>
        {{ $comments->links() }}
    </div>
</div>
