<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LegalPage extends Model
{
    use HasFactory;

    public const TERMS_SLUG = 'terms-and-conditions';
    public const PRIVACY_SLUG = 'privacy-policy';

    public const ALLOWED_SLUGS = [
        self::TERMS_SLUG,
        self::PRIVACY_SLUG,
    ];

    protected $fillable = [
        'slug',
        'title',
        'content',
    ];
}
