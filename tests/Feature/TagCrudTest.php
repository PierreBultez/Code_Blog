<?php

use App\Livewire\Dashboard\TagForm;
use App\Livewire\Dashboard\TagList;
use App\Models\Tag;
use App\Models\User;
use Livewire\Livewire;

it('renders tag list component', function () {
    $user = User::factory()->create();
    $this->actingAs($user)->get(route('dashboard.tags.index'))->assertSuccessful();
});

it('renders tag create component', function () {
    $user = User::factory()->create();
    $this->actingAs($user)->get(route('dashboard.tags.create'))->assertSuccessful();
});

it('can create a tag', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    Livewire::test(TagForm::class)
        ->set('name', 'New Tag')
        ->call('save')
        ->assertRedirect(route('dashboard.tags.index'));

    $this->assertDatabaseHas('tags', [
        'name' => 'New Tag',
    ]);
});

it('can edit a tag', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $tag = Tag::factory()->create(['name' => 'Old Tag']);

    Livewire::test(TagForm::class, ['tag' => $tag])
        ->set('name', 'Updated Tag')
        ->call('save')
        ->assertRedirect(route('dashboard.tags.index'));

    $this->assertDatabaseHas('tags', [
        'id' => $tag->id,
        'name' => 'Updated Tag',
    ]);
});

it('can delete a tag', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $tag = Tag::factory()->create();

    Livewire::test(TagList::class)
        ->call('deleteTag', $tag->id)
        ->assertSuccessful();

    $this->assertDatabaseMissing('tags', [
        'id' => $tag->id,
    ]);
});
