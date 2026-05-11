<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HomepageContent extends Model
{
    use HasFactory;

    public const DEFAULT_SITE_KEY = 'default';

    protected $fillable = [
        'site_key',
        'hero_title',
        'hero_description',
        'hero_image_path',
    ];

    public static function current(): self
    {
        return static::query()->where('site_key', static::DEFAULT_SITE_KEY)->first()
            ?? new static([
                'site_key' => static::DEFAULT_SITE_KEY,
                'hero_title' => 'Starlink Kenya | High-Speed Satellite Internet Across Kenya',
                'hero_description' => 'Starlink Kenya offers high-speed satellite internet with affordable packages, hardware, and monthly plans. Stay connected anywhere in Kenya today.',
            ]);
    }

    public function heroImageUrl(): ?string
    {
        if (!$this->hero_image_path) {
            return null;
        }

        return asset($this->hero_image_path);
    }
}
