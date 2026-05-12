<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class Page extends Model
{
    use HasFactory;

    protected $fillable = [
        'meta_title',
        'meta_description',
        'title',
        'heading_two',
        'slug',
        'image_url',
        'alt_text',
        'type',
        'body',
    ];

    public static function storageReady(): bool
    {
        return Schema::hasTable((new static())->getTable());
    }
}
