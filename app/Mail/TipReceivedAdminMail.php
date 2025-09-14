<?php

namespace App\Mail;

use App\Models\Tip;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TipReceivedAdminMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Tip $tip)
    {
    }

    public function build(): self
    {
        return $this->subject('New tip received (admin)')
            ->view('emails.tip-received-admin');
    }
}

