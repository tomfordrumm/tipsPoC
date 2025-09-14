<?php

namespace App\Mail;

use App\Models\PayoutRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PayoutRequestAdminMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public PayoutRequest $payout)
    {
    }

    public function build(): self
    {
        return $this->subject('New payout request')
            ->view('emails.payout-request-admin');
    }
}

