<?php

namespace App\Mail;

use App\Models\Comment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewCommentMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Comment $comment) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '[Commentaire] '.$this->comment->article->title,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.new-comment',
        );
    }

    /**
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
