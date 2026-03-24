<?php

namespace App\Livewire\Dashboard;

use App\Models\Article;
use Livewire\Component;
use Livewire\WithPagination;

class ArticleList extends Component
{
    use WithPagination;

    public function deleteArticle(Article $article): void
    {
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
