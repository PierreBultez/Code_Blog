<?php

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

it('uploads an image and returns its url', function () {
    Storage::fake('public');

    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->post(route('tinymce.upload'), [
            'file' => UploadedFile::fake()->image('photo.jpg', 800, 600),
        ]);

    $response->assertOk()
        ->assertJsonStructure(['location']);

    Storage::disk('public')->assertExists(
        str_replace(asset('storage/').'/', '', $response->json('location'))
    );
});

it('rejects non-image files', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->post(route('tinymce.upload'), [
            'file' => UploadedFile::fake()->create('document.pdf', 100),
        ]);

    $response->assertSessionHasErrors('file');
});

it('requires authentication', function () {
    $response = $this->post(route('tinymce.upload'), [
        'file' => UploadedFile::fake()->image('photo.jpg'),
    ]);

    $response->assertRedirect(route('login'));
});
