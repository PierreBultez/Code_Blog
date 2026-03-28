<?php

use App\Livewire\Dashboard\ArticleForm;
use App\Models\Article;
use App\Models\User;
use Livewire\Livewire;

it('strips script tags from article content on save', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    Livewire::test(ArticleForm::class)
        ->set('title', 'XSS Test Article')
        ->set('content', '<p>Safe content</p><script>alert("xss")</script>')
        ->call('save')
        ->assertRedirect(route('dashboard.articles.index'));

    $article = Article::where('title', 'XSS Test Article')->first();
    expect($article->content)->not->toContain('<script>');
    expect($article->content)->toContain('<p>Safe content</p>');
});

it('strips event handler attributes from article content', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    Livewire::test(ArticleForm::class)
        ->set('title', 'Event Handler Test')
        ->set('content', '<p onmouseover="alert(1)">Hover me</p>')
        ->call('save')
        ->assertRedirect(route('dashboard.articles.index'));

    $article = Article::where('title', 'Event Handler Test')->first();
    expect($article->content)->not->toContain('onmouseover');
    expect($article->content)->toContain('<p>Hover me</p>');
});

it('preserves allowed HTML tags in article content', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $content = '<h2>Title</h2><p>Text with <strong>bold</strong> and <a href="https://example.com" title="link">a link</a>.</p><pre><code>code block</code></pre><ul><li>item</li></ul>';

    Livewire::test(ArticleForm::class)
        ->set('title', 'Allowed Tags Test')
        ->set('content', $content)
        ->call('save')
        ->assertRedirect(route('dashboard.articles.index'));

    $article = Article::where('title', 'Allowed Tags Test')->first();
    expect($article->content)->toContain('<h2>');
    expect($article->content)->toContain('<strong>bold</strong>');
    expect($article->content)->toContain('<a href="https://example.com"');
    expect($article->content)->toContain('<pre>');
    expect($article->content)->toContain('<code>code block</code>');
    expect($article->content)->toContain('<ul>');
    expect($article->content)->toContain('<li>item</li>');
});

it('strips iframe and form elements from article content', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    Livewire::test(ArticleForm::class)
        ->set('title', 'Iframe Test')
        ->set('content', '<p>Content</p><iframe src="https://evil.com"></iframe><form action="/steal"><input type="text"></form>')
        ->call('save')
        ->assertRedirect(route('dashboard.articles.index'));

    $article = Article::where('title', 'Iframe Test')->first();
    expect($article->content)->not->toContain('<iframe');
    expect($article->content)->not->toContain('<form');
    expect($article->content)->not->toContain('<input');
});
