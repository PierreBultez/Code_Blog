<?php

namespace App\Livewire\Dashboard;

use App\Models\Article;
use App\Models\Tag;
use Livewire\Component;

class ArticleForm extends Component
{
    public ?Article $article = null;

    public string $title = '';

    public string $excerpt = '';

    public string $content = '';

    public bool $is_published = false;

    public array $selectedTags = [];

    public string $newTag = '';

    public function mount(?Article $article = null): void
    {
        if ($article && $article->exists) {
            $this->article = $article;
            $this->title = $article->title;
            $this->excerpt = $article->excerpt ?? '';
            $this->content = $article->content;
            $this->is_published = $article->is_published;
            $this->selectedTags = $article->tags->pluck('id')->map(fn ($id) => (string) $id)->toArray();
        }
    }

    public function addTag(): void
    {
        $this->validate([
            'newTag' => 'required|string|max:255|unique:tags,name',
        ]);

        $tag = Tag::create(['name' => $this->newTag]);

        $this->selectedTags[] = (string) $tag->id;
        $this->newTag = '';
    }

    public function save(): void
    {
        $this->validate([
            'title' => 'required|string|max:255',
            'excerpt' => 'nullable|string',
            'content' => 'required|string',
            'is_published' => 'boolean',
            'selectedTags' => 'array',
            'selectedTags.*' => 'exists:tags,id',
        ]);

        if (! $this->article) {
            $this->article = new Article;
        }

        $this->article->title = $this->title;
        $this->article->excerpt = $this->excerpt;
        $this->article->content = $this->content;
        $this->article->is_published = $this->is_published;

        if ($this->is_published && ! $this->article->published_at) {
            $this->article->published_at = now();
        } elseif (! $this->is_published) {
            $this->article->published_at = null;
        }

        $this->article->save();

        $this->article->tags()->sync($this->selectedTags);

        $this->redirectRoute('dashboard.articles.index');
    }

    public function render()
    {
        return view('livewire.dashboard.article-form', [
            'availableTags' => Tag::orderBy('name')->get(),
        ])->layout('layouts.app');
    }
}
