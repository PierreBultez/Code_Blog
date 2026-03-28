<?php

namespace App\Livewire\Dashboard;

use App\Models\Article;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\WithPagination;

class ArticleList extends Component
{
    use WithPagination;

    public function deleteArticle(Article $article): void
    {
        Log::channel('single')->info('Article deleted', [
            'article_id' => $article->id,
            'title' => $article->title,
            'user_id' => auth()->id(),
            'ip' => request()->ip(),
        ]);

        $article->delete();
        $this->resetPage();
    }

    public function render()
    {
        return view('livewire.dashboard.article-list', [
            'articles' => Article::latest()->paginate(10),
        ])->layout('layouts.app');
    }
}
