<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StripeEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'type',
        'signature',
        'payload',
        'processing_status',
        'processed_at',
        'payment_intent_id',
        'checkout_session_id',
        'tip_id',
        'error',
    ];

    protected $casts = [
        'payload' => 'array',
        'processed_at' => 'datetime',
    ];
}

