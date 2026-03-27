<?php

namespace App\Livewire\Dashboard;

use App\Models\Comment;
use Livewire\Component;
use Livewire\WithPagination;

class CommentList extends Component
{
    use WithPagination;

    public function deleteComment(Comment $comment): void
    {
        $comment->delete();
        $this->resetPage();
    }

    public function markAllAsRead(): void
    {
        Comment::where('is_read', false)->update(['is_read' => true]);
    }

    public function render()
    {
        Comment::where('is_read', false)
            ->whereIn('id', Comment::where('is_read', false)->latest()->paginate(10)->pluck('id'))
            ->update(['is_read' => true]);

        return view('livewire.dashboard.comment-list', [
            'comments' => Comment::with('article')->latest()->paginate(10),
            'unreadCount' => Comment::where('is_read', false)->count(),
        ])->layout('layouts.app');
    }
}
