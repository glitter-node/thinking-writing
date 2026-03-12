<?php

namespace App\Services;

use Illuminate\Contracts\Mail\Mailable;
use Illuminate\Support\Facades\Mail;

class MailService
{
    public function sendMail(string $to, string $subject, string $view, array $data = []): void
    {
        Mail::send($view, $data, function ($message) use ($to, $subject): void {
            $message->to($to)->subject($subject);
        });
    }

    public function sendMailable(string $to, Mailable $mailable): void
    {
        Mail::to($to)->send($mailable);
    }
}
