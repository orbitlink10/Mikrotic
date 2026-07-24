<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ProductImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'image_url',
        'is_primary',
        'sort_order',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function publicUrl(): ?string
    {
        $imageUrl = trim((string) $this->image_url);
        if ($imageUrl === '') {
            return null;
        }

        if (preg_match('#^(?:https?:)?//#i', $imageUrl) === 1 || Str::startsWith($imageUrl, 'data:image/')) {
            return $imageUrl;
        }

        $imageUrl = preg_replace('#^/?public/#', '', $imageUrl) ?? $imageUrl;

        if (Str::startsWith($imageUrl, '/')) {
            return $imageUrl;
        }

        return rtrim(request()->getBaseUrl(), '/') . '/' . ltrim($imageUrl, '/');
    }
}
