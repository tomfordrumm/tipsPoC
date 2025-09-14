<?php

namespace App\Mail;

use App\Models\Tip;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TipReceivedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Tip $tip)
    {
    }

    public function build(): self
    {
        return $this->subject('You received a new tip')
            ->view('emails.tip-received');
    }
}

