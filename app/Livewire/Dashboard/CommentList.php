<?php

namespace App\Livewire\Dashboard;

use App\Models\Comment;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\WithPagination;

class CommentList extends Component
{
    use WithPagination;

    public function deleteComment(Comment $comment): void
    {
        Log::channel('single')->info('Comment deleted', [
            'comment_id' => $comment->id,
            'author' => $comment->author_name,
            'article_id' => $comment->article_id,
            'user_id' => auth()->id(),
            'ip' => request()->ip(),
        ]);

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
