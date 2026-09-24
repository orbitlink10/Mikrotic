<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class Testimonial extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'role',
        'quote',
        'rating',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'rating' => 'integer',
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    public function scopeVisible(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderByDesc('id');
    }

    public static function storageReady(): bool
    {
        return Schema::hasTable((new static())->getTable());
    }

    /**
     * @return Collection<int, static>
     */
    public static function homepageItems(): Collection
    {
        if (!static::storageReady()) {
            return static::defaultItems();
        }

        return static::query()
            ->visible()
            ->ordered()
            ->get();
    }

    /**
     * @return Collection<int, static>
     */
    public static function defaultItems(): Collection
    {
        return collect();
    }
}
