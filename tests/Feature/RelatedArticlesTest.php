<?php

use App\Models\Article;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('shows related articles with shared tags', function () {
    $tag = Tag::factory()->create(['name' => 'Laravel']);

    $article = Article::factory()->published()->create(['title' => 'Article principal']);
    $article->tags()->attach($tag);

    $related = Article::factory()->published()->create(['title' => 'Article lié']);
    $related->tags()->attach($tag);

    $response = $this->get(route('articles.show', $article));

    $response->assertOk();
    $response->assertSee('Articles connexes');
    $response->assertSee('Article lié');
});

it('does not show unrelated articles', function () {
    $tagLaravel = Tag::factory()->create(['name' => 'Laravel']);
    $tagVue = Tag::factory()->create(['name' => 'Vue']);

    $article = Article::factory()->published()->create(['title' => 'Article Laravel']);
    $article->tags()->attach($tagLaravel);

    $unrelated = Article::factory()->published()->create(['title' => 'Article Vue']);
    $unrelated->tags()->attach($tagVue);

    $response = $this->get(route('articles.show', $article));

    $response->assertDontSee('Article Vue');
});

it('does not show the current article in related', function () {
    $tag = Tag::factory()->create(['name' => 'PHP']);

    $article = Article::factory()->published()->create(['title' => 'Mon article unique']);
    $article->tags()->attach($tag);

    $response = $this->get(route('articles.show', $article));

    $response->assertDontSee('Articles connexes');
});

it('limits related articles to 3', function () {
    $tag = Tag::factory()->create(['name' => 'Laravel']);

    $article = Article::factory()->published()->create();
    $article->tags()->attach($tag);

    for ($i = 0; $i < 5; $i++) {
        $related = Article::factory()->published()->create();
        $related->tags()->attach($tag);
    }

    $response = $this->get(route('articles.show', $article));

    expect(substr_count($response->getContent(), 'Articles connexes'))->toBe(1);
    expect(substr_count($response->getContent(), 'aspect-[1.91/1]'))->toBe(3);
});
