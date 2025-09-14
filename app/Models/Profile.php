<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Profile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'slug',
        'display_name',
        'bio',
        'avatar_path',
        'review_url',
        'quick_amounts',
    ];

    protected $casts = [
        'quick_amounts' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Generate a unique, lowercase slug from a given name.
     */
    public static function generateUniqueSlug(string $name): string
    {
        $base = Str::slug($name);
        if (strlen($base) < 3) {
            $base = 'user';
        }

        // Ensure max length before adding suffixes
        $base = substr($base, 0, 45);

        $slug = $base;
        $suffix = 2;

        while (self::where('slug', $slug)->exists()) {
            // Keep under 50 chars including hyphen and suffix
            $trimmed = substr($base, 0, max(1, 50 - (strlen((string)$suffix) + 1)));
            $slug = $trimmed.'-'.$suffix;
            $suffix++;
            if ($suffix > 200) { // fail-safe to avoid long loops
                $slug = $trimmed.'-'.Str::lower(Str::random(5));
                break;
            }
        }

        return Str::lower($slug);
    }
}
