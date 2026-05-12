<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'meta_description',
        'slug',
        'parent_id',
        'image_url',
        'description',
    ];

    public static function contentFieldsReady(): bool
    {
        $table = (new static())->getTable();

        return Schema::hasTable($table)
            && Schema::hasColumn($table, 'meta_description')
            && Schema::hasColumn($table, 'description');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
