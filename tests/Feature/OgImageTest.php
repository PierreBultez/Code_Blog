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

it('generates a resized og image with w parameter', function () {
    $article = Article::factory()->published()->create();

    $response = $this->get(route('articles.og-image', $article).'?w=400');

    $response->assertOk();
    $response->assertHeader('Content-Type', 'image/png');

    $image = imagecreatefromstring($response->content());
    expect(imagesx($image))->toBe(400);
    expect(imagesy($image))->toBe(210);
    imagedestroy($image);
});

it('ignores invalid w parameter and returns full size', function () {
    $article = Article::factory()->published()->create();

    $response = $this->get(route('articles.og-image', $article).'?w=999');

    $response->assertOk();

    $image = imagecreatefromstring($response->content());
    expect(imagesx($image))->toBe(1200);
    imagedestroy($image);
});

it('returns immutable cache headers with one year max-age', function () {
    $article = Article::factory()->published()->create();

    $response = $this->get(route('articles.og-image', $article));

    $cacheControl = $response->headers->get('Cache-Control');
    expect($cacheControl)->toContain('max-age=31536000')
        ->toContain('public')
        ->toContain('immutable');
});

it('generates srcset for dynamic og images', function () {
    $article = Article::factory()->published()->create(['og_image' => null]);

    $srcset = $article->ogImageSrcset();

    expect($srcset)->toContain('?w=400 400w')
        ->toContain('?w=800 800w')
        ->toContain('?w=1200 1200w');
});

it('returns empty srcset for uploaded og images', function () {
    $article = Article::factory()->published()->create([
        'og_image' => 'og-images/custom.png',
    ]);

    expect($article->ogImageSrcset())->toBe('');
});
