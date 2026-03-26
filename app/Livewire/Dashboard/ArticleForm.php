<?php

namespace App\Livewire\Dashboard;

use App\Models\Article;
use App\Models\Tag;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

class ArticleForm extends Component
{
    use WithFileUploads;

    public ?Article $article = null;

    public string $title = '';

    public string $excerpt = '';

    public string $meta_description = '';

    public string $og_text = '';

    public string $content = '';

    public bool $is_published = false;

    /** @var TemporaryUploadedFile|null */
    public $og_image_upload = null;

    public bool $remove_og_image = false;

    public array $selectedTags = [];

    public string $newTag = '';

    public function mount(?Article $article = null): void
    {
        if ($article && $article->exists) {
            $this->article = $article;
            $this->title = $article->title;
            $this->excerpt = $article->excerpt ?? '';
            $this->meta_description = $article->meta_description ?? '';
            $this->og_text = $article->og_text ?? '';
            $this->content = $article->content;
            $this->is_published = $article->is_published;
            $this->selectedTags = $article->tags->pluck('id')->map(fn ($id) => (string) $id)->toArray();
        }
    }

    public function removeOgImage(): void
    {
        $this->remove_og_image = true;
        $this->og_image_upload = null;
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
            'meta_description' => 'nullable|string|max:160',
            'og_text' => 'nullable|string|max:100',
            'content' => 'required|string',
            'is_published' => 'boolean',
            'og_image_upload' => 'nullable|image|mimes:png,jpg,jpeg,webp|max:2048|dimensions:min_width=1200,min_height=630',
            'selectedTags' => 'array',
            'selectedTags.*' => 'exists:tags,id',
        ]);

        if (! $this->article) {
            $this->article = new Article;
        }

        $this->article->title = $this->title;
        $this->article->excerpt = $this->excerpt;
        $this->article->meta_description = $this->meta_description ?: null;
        $this->article->og_text = $this->og_text ?: null;
        $this->article->content = $this->content;
        $this->article->is_published = $this->is_published;

        // Gestion de l'image OG
        if ($this->og_image_upload) {
            // Supprimer l'ancienne image si elle existe
            if ($this->article->og_image) {
                Storage::disk('public')->delete($this->article->og_image);
            }

            $this->article->og_image = $this->og_image_upload->store('og-images', 'public');
        } elseif ($this->remove_og_image && $this->article->og_image) {
            Storage::disk('public')->delete($this->article->og_image);
            $this->article->og_image = null;
        }

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
