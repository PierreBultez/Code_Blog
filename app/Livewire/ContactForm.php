<?php

namespace App\Livewire;

use App\Mail\ContactConfirmationMail;
use App\Mail\ContactMessageMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Component;

class ContactForm extends Component
{
    public string $name = '';

    public string $email = '';

    public string $subject = 'Développement';

    public string $message = '';

    public string $honeypot = '';

    public bool $sent = false;

    public function send(): void
    {
        if ($this->honeypot !== '') {
            $this->reset(['name', 'email', 'subject', 'message', 'honeypot']);
            $this->sent = true;

            return;
        }

        $key = 'contact:'.request()->ip();

        if (RateLimiter::tooManyAttempts($key, 3)) {
            $this->addError('message', 'Trop de messages envoyés. Réessayez dans quelques minutes.');

            return;
        }

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'subject' => ['required', 'string', 'in:Développement,Infrastructure / SysAdmin,Autre'],
            'message' => ['required', 'string', 'min:10', 'max:5000'],
        ]);

        Mail::to(config('mail.contact_recipient'))
            ->send(new ContactMessageMail($validated));

        Mail::to($validated['email'])
            ->send(new ContactConfirmationMail($validated));

        RateLimiter::hit($key, 300);

        $this->reset(['name', 'email', 'subject', 'message', 'honeypot']);
        $this->sent = true;
    }

    public function render()
    {
        return view('livewire.contact-form');
    }
}
