<?php

use App\Livewire\ContactForm;
use App\Mail\ContactConfirmationMail;
use App\Mail\ContactMessageMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Livewire;

it('renders the contact form on the about page', function () {
    $this->get(route('about'))
        ->assertSuccessful()
        ->assertSeeLivewire(ContactForm::class);
});

it('sends contact and confirmation emails on valid submission', function () {
    Mail::fake();

    Livewire::test(ContactForm::class)
        ->set('name', 'Jean Dupont')
        ->set('email', 'jean@example.com')
        ->set('subject', 'Développement')
        ->set('message', 'Bonjour, je souhaite discuter de mon projet.')
        ->call('send')
        ->assertHasNoErrors()
        ->assertSet('sent', true)
        ->assertSet('name', '')
        ->assertSet('email', '')
        ->assertSet('message', '');

    Mail::assertSent(ContactMessageMail::class, function ($mail) {
        return $mail->hasTo(config('mail.contact_recipient'));
    });

    Mail::assertSent(ContactConfirmationMail::class, function ($mail) {
        return $mail->hasTo('jean@example.com');
    });
});

it('validates required fields', function () {
    Livewire::test(ContactForm::class)
        ->set('name', '')
        ->set('email', '')
        ->set('message', '')
        ->call('send')
        ->assertHasErrors(['name', 'email', 'message']);
});

it('validates email format', function () {
    Livewire::test(ContactForm::class)
        ->set('email', 'not-an-email')
        ->call('send')
        ->assertHasErrors(['email']);
});

it('validates message minimum length', function () {
    Livewire::test(ContactForm::class)
        ->set('name', 'Jean')
        ->set('email', 'jean@example.com')
        ->set('subject', 'Autre')
        ->set('message', 'Court')
        ->call('send')
        ->assertHasErrors(['message']);
});

it('validates subject is in allowed list', function () {
    Livewire::test(ContactForm::class)
        ->set('name', 'Jean')
        ->set('email', 'jean@example.com')
        ->set('subject', 'Sujet invalide')
        ->set('message', 'Un message suffisamment long.')
        ->call('send')
        ->assertHasErrors(['subject']);
});

it('silently discards honeypot submissions without sending emails', function () {
    Mail::fake();

    Livewire::test(ContactForm::class)
        ->set('name', 'Bot')
        ->set('email', 'bot@spam.com')
        ->set('subject', 'Développement')
        ->set('message', 'Buy cheap stuff now!')
        ->set('honeypot', 'I am a bot')
        ->call('send')
        ->assertHasNoErrors()
        ->assertSet('sent', true)
        ->assertSet('name', '')
        ->assertSet('email', '');

    Mail::assertNothingSent();
});

it('rate limits contact form submissions', function () {
    Mail::fake();
    RateLimiter::clear('contact:127.0.0.1');

    $component = Livewire::test(ContactForm::class);

    for ($i = 0; $i < 3; $i++) {
        $component
            ->set('name', 'Jean')
            ->set('email', 'jean@example.com')
            ->set('subject', 'Développement')
            ->set('message', 'Message de test numéro '.($i + 1))
            ->call('send');
    }

    $component
        ->set('sent', false)
        ->set('name', 'Jean')
        ->set('email', 'jean@example.com')
        ->set('subject', 'Développement')
        ->set('message', 'Ce message devrait être bloqué.')
        ->call('send')
        ->assertHasErrors(['message']);

    Mail::assertSentCount(6); // 3 contact + 3 confirmation
});
