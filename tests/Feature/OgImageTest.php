<?php

use App\Models\Article;

it('generates an og image for a published article', function () {
    $article = Article::factory()->published()->create();

    $response = $this->get(route('articles.og-image', $article));

    $response->assertOk();
    $response->assertHeader('Content-Type', 'image/png');
});

it('returns 404 for a draft article og image', function () {
    $article = Article::factory()->draft()->create();

    $response = $this->get("/articles/{$article->slug}/og-image.png");

    $response->assertNotFound();
});

it('returns the uploaded og image url when set', function () {
    $article = Article::factory()->published()->create([
        'og_image' => 'og-images/custom.png',
    ]);

    expect($article->og_image_url)->toContain('storage/og-images/custom.png');
});

it('returns the generated og image url when no upload', function () {
    $article = Article::factory()->published()->create([
        'og_image' => null,
    ]);

    expect($article->og_image_url)->toContain('/og-image.png');
});
