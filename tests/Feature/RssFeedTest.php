<?php

use App\Models\Article;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('returns valid rss xml with correct content type', function () {
    Article::factory()->published()->create();

    $response = $this->get('/feed');

    $response->assertOk();
    $response->assertHeader('Content-Type', 'application/rss+xml; charset=UTF-8');
    $response->assertSee('<?xml version="1.0" encoding="UTF-8"?>', false);
    $response->assertSee('<rss version="2.0"', false);
});

it('includes published articles', function () {
    $article = Article::factory()->published()->create(['title' => 'Mon super article']);

    $response = $this->get('/feed');

    $response->assertSee('Mon super article');
    $response->assertSee(route('articles.show', $article->slug));
});

it('excludes draft articles', function () {
    $article = Article::factory()->draft()->create(['title' => 'Brouillon caché']);

    $response = $this->get('/feed');

    $response->assertDontSee('Brouillon caché');
});

it('limits to 20 articles', function () {
    Article::factory()->published()->count(25)->create();

    $response = $this->get('/feed');

    expect(substr_count($response->getContent(), '<item>'))->toBe(20);
});
