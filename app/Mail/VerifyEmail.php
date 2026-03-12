<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class VerifyEmail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $verifyUrl,
        public string $email,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Glitter Thought Write 이메일 인증',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.verify',
            with: [
                'verifyUrl' => $this->verifyUrl,
                'email' => $this->email,
            ],
        );
    }
}
