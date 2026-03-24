<?php

use App\Models\Article;
use App\Models\Tag;
use Illuminate\Support\Str;

it('can create an article', function () {
    $article = Article::factory()->create([
        'title' => 'My First Article',
    ]);

    $this->assertDatabaseHas('articles', [
        'title' => 'My First Article',
        'slug' => Str::slug('My First Article'),
    ]);

    expect($article->is_published)->toBeBool();
});

it('can create a tag', function () {
    $tag = Tag::factory()->create([
        'name' => 'Laravel',
    ]);

    $this->assertDatabaseHas('tags', [
        'name' => 'Laravel',
        'slug' => 'laravel',
    ]);
});

it('can attach tags to an article', function () {
    $article = Article::factory()->create();
    $tags = Tag::factory(3)->create();

    $article->tags()->attach($tags);

    expect($article->tags)->toHaveCount(3);
    $this->assertDatabaseCount('article_tag', 3);
});
