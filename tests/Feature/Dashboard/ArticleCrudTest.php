<?php

use App\Livewire\Dashboard\ArticleForm;
use App\Livewire\Dashboard\ArticleList;
use App\Models\Article;
use App\Models\Tag;
use App\Models\User;
use Livewire\Livewire;

it('requires authentication to view articles list', function () {
    $this->get('/dashboard/articles')->assertRedirect('/login');
});

it('can view the articles list when authenticated', function () {
    $user = User::factory()->create();
    $this->actingAs($user)->get('/dashboard/articles')->assertSuccessful();
});

it('can create a new article with tags', function () {
    $tag1 = Tag::factory()->create(['name' => 'Laravel']);
    $tag2 = Tag::factory()->create(['name' => 'PHP']);

    Livewire::test(ArticleForm::class)
        ->set('title', 'Testing Livewire Forms')
        ->set('content', '<p>Some rich text content</p>')
        ->set('is_published', true)
        ->set('selectedTags', [(string) $tag1->id])
        ->call('save')
        ->assertRedirect(route('dashboard.articles.index'));

    $this->assertDatabaseHas('articles', [
        'title' => 'Testing Livewire Forms',
        'is_published' => true,
    ]);

    $article = Article::where('title', 'Testing Livewire Forms')->first();
    expect($article->tags)->toHaveCount(1);
    expect($article->tags->first()->name)->toBe('Laravel');
});

it('can edit an existing article', function () {
    $article = Article::factory()->create(['title' => 'Old Title']);

    Livewire::test(ArticleForm::class, ['article' => $article])
        ->set('title', 'New Updated Title')
        ->call('save');

    $this->assertDatabaseHas('articles', [
        'id' => $article->id,
        'title' => 'New Updated Title',
    ]);
});

it('can delete an article', function () {
    $article = Article::factory()->create();

    Livewire::test(ArticleList::class)
        ->call('deleteArticle', $article->id);

    $this->assertDatabaseMissing('articles', [
        'id' => $article->id,
    ]);
});
