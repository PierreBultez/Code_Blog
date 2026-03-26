<?php

namespace App\Livewire;

use App\Mail\ContactConfirmationMail;
use App\Mail\ContactMessageMail;
use Illuminate\Support\Facades\Mail;
use Livewire\Component;

class ContactForm extends Component
{
    public string $name = '';

    public string $email = '';

    public string $subject = 'Développement';

    public string $message = '';

    public bool $sent = false;

    public function send(): void
    {
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

        $this->reset(['name', 'email', 'subject', 'message']);
        $this->sent = true;
    }

    public function render()
    {
        return view('livewire.contact-form');
    }
}
