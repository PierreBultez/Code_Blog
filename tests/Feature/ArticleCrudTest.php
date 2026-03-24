<?php

use App\Livewire\Dashboard\ArticleForm;
use App\Livewire\Dashboard\ArticleList;
use App\Models\Article;
use App\Models\Tag;
use App\Models\User;
use Livewire\Livewire;

it('renders article list component', function () {
    $user = User::factory()->create();
    $this->actingAs($user)->get(route('dashboard.articles.index'))->assertSuccessful();
});

it('renders article create component', function () {
    $user = User::factory()->create();
    $this->actingAs($user)->get(route('dashboard.articles.create'))->assertSuccessful();
});

it('can create an article', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $tag = Tag::factory()->create();

    Livewire::test(ArticleForm::class)
        ->set('title', 'My New Article')
        ->set('excerpt', 'This is an excerpt')
        ->set('content', '<p>This is the content</p>')
        ->set('is_published', true)
        ->set('selectedTags', [(string) $tag->id])
        ->call('save')
        ->assertRedirect(route('dashboard.articles.index'));

    $this->assertDatabaseHas('articles', [
        'title' => 'My New Article',
        'is_published' => true,
    ]);

    $article = Article::where('title', 'My New Article')->first();
    $this->assertNotNull($article->published_at);
    $this->assertTrue($article->tags->contains($tag));
});

it('can add a new tag while creating an article', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    Livewire::test(ArticleForm::class)
        ->set('newTag', 'Inline Tag')
        ->call('addTag')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('tags', [
        'name' => 'Inline Tag',
    ]);
});

it('can edit an article', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $article = Article::factory()->create([
        'title' => 'Old Title',
        'is_published' => false,
    ]);

    Livewire::test(ArticleForm::class, ['article' => $article])
        ->set('title', 'Updated Title')
        ->set('is_published', true)
        ->call('save')
        ->assertRedirect(route('dashboard.articles.index'));

    $this->assertDatabaseHas('articles', [
        'id' => $article->id,
        'title' => 'Updated Title',
        'is_published' => true,
    ]);
});

it('can delete an article', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $article = Article::factory()->create();

    Livewire::test(ArticleList::class)
        ->call('deleteArticle', $article->id)
        ->assertSuccessful();

    $this->assertDatabaseMissing('articles', [
        'id' => $article->id,
    ]);
});
