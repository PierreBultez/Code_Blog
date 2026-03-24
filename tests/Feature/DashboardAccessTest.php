<?php

use App\Models\User;
use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

it('redirects unauthenticated users to login', function () {
    get(route('dashboard'))->assertRedirect(route('login'));
    get(route('dashboard.articles.index'))->assertRedirect(route('login'));
    get(route('dashboard.tags.index'))->assertRedirect(route('login'));
    get(route('dashboard.articles.create'))->assertRedirect(route('login'));
    get(route('dashboard.tags.create'))->assertRedirect(route('login'));
});

it('allows authenticated users to access dashboard', function () {
    $user = User::factory()->create();

    actingAs($user)->get(route('dashboard'))->assertSuccessful();
    actingAs($user)->get(route('dashboard.articles.index'))->assertSuccessful();
    actingAs($user)->get(route('dashboard.tags.index'))->assertSuccessful();
});
