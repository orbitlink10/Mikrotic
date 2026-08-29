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
        return collect([
            [
                'name' => 'Joan K., Nairobi',
                'role' => 'Customer',
                'quote' => 'The team helped us choose the right lights for the gate and parking area, then explained the best mounting position before delivery.',
                'rating' => 5,
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'name' => 'Samuel O., Meru',
                'role' => 'Customer',
                'quote' => 'The motion sensor lights have made the compound brighter at night without increasing our electricity bill.',
                'rating' => 5,
                'sort_order' => 2,
                'is_active' => true,
            ],
            [
                'name' => 'Victor M., Rongai',
                'role' => 'Customer',
                'quote' => 'We needed lighting for a farm store and walkway. The product pages made it easy to compare wattage, price and stock before ordering.',
                'rating' => 5,
                'sort_order' => 3,
                'is_active' => true,
            ],
        ])->map(fn (array $attributes): static => new static($attributes));
    }
}
