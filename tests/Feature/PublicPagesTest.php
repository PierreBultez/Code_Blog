<?php

use App\Models\Article;
use App\Models\Tag;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

// ── Pages publiques : status 200 ──

it('affiche la page d\'accueil', function () {
    $this->get(route('home'))->assertSuccessful();
});

it('affiche la page articles', function () {
    $this->get(route('articles.index'))->assertSuccessful();
});

it('affiche la page à propos', function () {
    $this->get(route('about'))->assertSuccessful();
});

// ── Home : articles publiés vs brouillons ──

it('affiche les articles publiés sur la page d\'accueil', function () {
    $article = Article::factory()->published()->create(['title' => 'Article Visible']);

    $this->get(route('home'))
        ->assertSuccessful()
        ->assertSee('Article Visible');
});

it('n\'affiche pas les brouillons sur la page d\'accueil', function () {
    $article = Article::factory()->draft()->create(['title' => 'Brouillon Secret']);

    $this->get(route('home'))
        ->assertSuccessful()
        ->assertDontSee('Brouillon Secret');
});

// ── Articles index : publiés, brouillons, pagination ──

it('affiche uniquement les articles publiés sur la liste', function () {
    Article::factory()->published()->create(['title' => 'Publié OK']);
    Article::factory()->draft()->create(['title' => 'Draft Caché']);

    $this->get(route('articles.index'))
        ->assertSuccessful()
        ->assertSee('Publié OK')
        ->assertDontSee('Draft Caché');
});

it('pagine les articles correctement', function () {
    Article::factory()->published()->count(12)->create();

    $this->get(route('articles.index'))
        ->assertSuccessful();

    $this->get(route('articles.index', ['page' => 2]))
        ->assertSuccessful();
});

// ── Article show : publié, brouillon, slug inexistant ──

it('affiche un article publié', function () {
    $article = Article::factory()->published()->create();

    $this->get(route('articles.show', $article))
        ->assertSuccessful()
        ->assertSee($article->title);
});

it('retourne 404 pour un article brouillon', function () {
    $article = Article::factory()->draft()->create();

    $this->get(route('articles.show', $article))
        ->assertNotFound();
});

it('retourne 404 pour un slug inexistant', function () {
    $this->get('/articles/slug-qui-nexiste-pas')
        ->assertNotFound();
});

// ── Filtrage par tag ──

it('filtre les articles par tag', function () {
    $tagLaravel = Tag::factory()->create(['name' => 'Laravel', 'slug' => 'laravel']);
    $tagDevops = Tag::factory()->create(['name' => 'DevOps', 'slug' => 'devops']);

    $articleLaravel = Article::factory()->published()->create(['title' => 'Article Laravel']);
    $articleLaravel->tags()->attach($tagLaravel);

    $articleDevops = Article::factory()->published()->create(['title' => 'Article DevOps']);
    $articleDevops->tags()->attach($tagDevops);

    $this->get(route('articles.index', ['tag' => 'laravel']))
        ->assertSuccessful()
        ->assertSee('Article Laravel')
        ->assertDontSee('Article DevOps');
});

it('affiche tous les articles sans filtre tag', function () {
    $tag = Tag::factory()->create(['name' => 'PHP', 'slug' => 'php']);

    $article1 = Article::factory()->published()->create(['title' => 'Avec Tag']);
    $article1->tags()->attach($tag);

    $article2 = Article::factory()->published()->create(['title' => 'Sans Tag']);

    $this->get(route('articles.index'))
        ->assertSuccessful()
        ->assertSee('Avec Tag')
        ->assertSee('Sans Tag');
});

// ── Scope published ──

it('filtre correctement avec le scope published', function () {
    Article::factory()->published()->count(3)->create();
    Article::factory()->draft()->count(2)->create();

    expect(Article::query()->published()->count())->toBe(3);
});
