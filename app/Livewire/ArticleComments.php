<?php

namespace App\Livewire;

use App\Mail\NewCommentMail;
use App\Models\Article;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Component;

class ArticleComments extends Component
{
    public Article $article;

    public string $author_name = '';

    public string $content = '';

    public string $honeypot = '';

    public bool $sent = false;

    public function addComment(): void
    {
        if ($this->honeypot !== '') {
            $this->reset(['author_name', 'content', 'honeypot']);
            $this->sent = true;

            return;
        }

        $key = 'comment:'.request()->ip();

        if (RateLimiter::tooManyAttempts($key, 3)) {
            $this->addError('content', 'Vous postez trop de commentaires. Réessayez dans quelques minutes.');

            return;
        }

        $this->validate([
            'author_name' => ['required', 'string', 'max:50'],
            'content' => ['required', 'string', 'max:2000'],
        ]);

        $comment = $this->article->comments()->create([
            'author_name' => $this->author_name,
            'content' => $this->content,
        ]);

        RateLimiter::hit($key, 300);

        Mail::to(config('mail.contact_recipient'))
            ->send(new NewCommentMail($comment));

        $this->reset(['content', 'honeypot']);
        $this->sent = true;
    }

    public function render()
    {
        return view('livewire.article-comments', [
            'comments' => $this->article->comments()->oldest()->get(),
        ]);
    }
}
