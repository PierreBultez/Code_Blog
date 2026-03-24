<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

it('redirects registration page to login', function () {
    $this->get('/register')->assertRedirect('/login');
});

it('disallows posting to register endpoint', function () {
    $this->post('/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ])->assertRedirect('/login');
});

it('allows the admin to login', function () {
    $user = User::factory()->create([
        'email' => 'pierre@example.com',
        'password' => Hash::make('password'),
    ]);

    $this->post('/login', [
        'email' => 'pierre@example.com',
        'password' => 'password',
    ])->assertRedirect('/dashboard');

    $this->assertAuthenticatedAs($user);
});
