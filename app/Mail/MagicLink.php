<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MagicLink extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $magicUrl,
        public string $email,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Glitter Thought Write sign-in link',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.magic-link',
            with: [
                'magicUrl' => $this->magicUrl,
                'email' => $this->email,
            ],
        );
    }
}
