<?php

use App\Models\Article;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('returns valid xml with correct content type', function () {
    Article::factory()->published()->create(['title' => 'Mon article test']);

    $response = $this->get('/sitemap.xml');

    $response->assertOk();
    $response->assertHeader('Content-Type', 'application/xml');
    $response->assertSee('<?xml version="1.0" encoding="UTF-8"?>', false);
    $response->assertSee('<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">', false);
});

it('includes published articles', function () {
    $article = Article::factory()->published()->create(['title' => 'Article visible']);

    $response = $this->get('/sitemap.xml');

    $response->assertSee(route('articles.show', $article->slug));
});

it('excludes draft articles', function () {
    $article = Article::factory()->draft()->create(['title' => 'Brouillon secret']);

    $response = $this->get('/sitemap.xml');

    $response->assertDontSee($article->slug);
});

it('includes static pages', function () {
    $response = $this->get('/sitemap.xml');

    $response->assertSee(route('home'));
    $response->assertSee(route('articles.index'));
    $response->assertSee(route('about'));
});
