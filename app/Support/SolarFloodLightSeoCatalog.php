<?php

namespace App\Support;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class SolarFloodLightSeoCatalog
{
    public const PRICE_AUTHORITY_SLUG = 'solar-flood-lights-price-in-kenya';

    /**
     * Backwards-compatible alias for older code/tests that still refer to the
     * previous product domain.
     */
    public const ROUTER_AUTHORITY_SLUG = self::PRICE_AUTHORITY_SLUG;

    /**
     * @return array<string, array{name: string, meta_description: string, description: string}>
     */
    public static function primaryCategories(): array
    {
        return [
            self::PRICE_AUTHORITY_SLUG => [
                'name' => 'Solar Flood Lights Price in Kenya',
                'meta_description' => 'Compare solar flood light prices in Kenya for homes, compounds, farms, businesses and security lighting projects.',
                'description' => '<p>Compare solar flood lights available in Kenya by wattage, battery capacity, panel type, stock status and recommended use.</p>',
            ],
            'outdoor-solar-flood-lights' => [
                'name' => 'Outdoor Solar Flood Lights',
                'meta_description' => 'Shop outdoor solar flood lights in Kenya for gates, yards, parking areas, farms and commercial compounds.',
                'description' => '<p>Find outdoor solar flood lights for reliable night lighting in homes, businesses, farms and perimeter areas.</p>',
            ],
            'motion-sensor-solar-lights' => [
                'name' => 'Motion Sensor Solar Lights',
                'meta_description' => 'Buy motion sensor solar lights in Kenya for gates, walkways, security zones and energy-saving outdoor lighting.',
                'description' => '<p>Browse solar lights with motion sensors for security, automatic activation and longer battery runtime.</p>',
            ],
            'solar-street-lights' => [
                'name' => 'Solar Street Lights',
                'meta_description' => 'Solar street lights in Kenya for roads, estates, schools, churches, parking areas and public outdoor spaces.',
                'description' => '<p>Compare all-in-one and split solar street lights for paths, roads, estates and public-area lighting.</p>',
            ],
            'solar-security-lights' => [
                'name' => 'Solar Security Lights',
                'meta_description' => 'Solar security lights in Kenya for perimeter walls, entrances, CCTV zones and commercial properties.',
                'description' => '<p>Choose bright solar security lights for entrances, perimeter walls, loading areas and outdoor surveillance zones.</p>',
            ],
            'solar-garden-wall-lights' => [
                'name' => 'Solar Garden & Wall Lights',
                'meta_description' => 'Decorative and practical solar garden lights, wall lights and pathway lights for Kenyan homes and compounds.',
                'description' => '<p>Shop compact solar garden, wall and pathway lights for homes, patios, walkways and small outdoor spaces.</p>',
            ],
            'solar-panels-batteries' => [
                'name' => 'Solar Panels & Batteries',
                'meta_description' => 'Solar lighting panels, batteries and replacement power parts for solar flood lights and street lights in Kenya.',
                'description' => '<p>Find solar panels, batteries and power components for maintaining and upgrading solar lighting installations.</p>',
            ],
            'solar-lighting-accessories' => [
                'name' => 'Installation Accessories',
                'meta_description' => 'Solar light installation accessories in Kenya including poles, brackets, cables, remotes and mounting kits.',
                'description' => '<p>Browse poles, brackets, remotes, cables and mounting accessories for solar flood lights and street lights.</p>',
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function categoryTitles(): array
    {
        return [
            self::PRICE_AUTHORITY_SLUG => 'Solar Flood Lights Price in Kenya | Buy Solar Flood Lights',
            'outdoor-solar-flood-lights' => 'Outdoor Solar Flood Lights in Kenya',
            'motion-sensor-solar-lights' => 'Motion Sensor Solar Lights in Kenya',
            'solar-street-lights' => 'Solar Street Lights in Kenya',
            'solar-security-lights' => 'Solar Security Lights in Kenya',
            'solar-garden-wall-lights' => 'Solar Garden & Wall Lights in Kenya',
            'solar-panels-batteries' => 'Solar Light Panels & Batteries in Kenya',
            'solar-lighting-accessories' => 'Solar Lighting Accessories in Kenya',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function comparisonPages(): array
    {
        return [
            '100w-vs-200w-solar-flood-lights' => '100W vs 200W Solar Flood Lights',
            '200w-vs-300w-solar-flood-lights' => '200W vs 300W Solar Flood Lights',
            'motion-sensor-vs-standard-solar-lights' => 'Motion Sensor vs Standard Solar Lights',
            'all-in-one-vs-split-solar-lights' => 'All-in-One vs Split Solar Lights',
        ];
    }

    /**
     * @return array<string, array{0: string, 1: string}>
     */
    public static function comparisonProducts(): array
    {
        return [
            '100w-vs-200w-solar-flood-lights' => ['100W', '200W'],
            '200w-vs-300w-solar-flood-lights' => ['200W', '300W'],
            'motion-sensor-vs-standard-solar-lights' => ['Motion Sensor', '100W Solar Flood Light'],
            'all-in-one-vs-split-solar-lights' => ['All-in-One', 'Split Solar Flood Light'],
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function resolvableComparisonSlugs(): array
    {
        $comparisons = self::comparisonProducts();
        if ($comparisons === []) {
            return [];
        }

        $products = Product::query()->active()->get(['id', 'name', 'slug', 'sku']);
        $resolvable = [];

        foreach ($comparisons as $slug => [$left, $right]) {
            if (self::findMatchingProduct($products, $left) && self::findMatchingProduct($products, $right)) {
                $resolvable[] = $slug;
            }
        }

        return $resolvable;
    }

    public static function navLabel(Category $category): string
    {
        $slug = Str::slug($category->slug);

        if ($slug === self::PRICE_AUTHORITY_SLUG || self::isPriceAuthorityCategory($category)) {
            return 'Solar Flood Lights';
        }

        if ($mapped = self::primaryCategories()[$slug]['name'] ?? null) {
            return $mapped;
        }

        $name = trim((string) $category->name);
        $name = preg_replace('/\s*[-|:]\s*price(s)?\s*in\s*kenya\s*$/iu', '', $name) ?? $name;
        $name = preg_replace('/\s*price(s)?\s*in\s*kenya\s*$/iu', '', $name) ?? $name;
        $name = preg_replace('/\s*for\s*sale\s*in\s*kenya\s*$/iu', '', $name) ?? $name;

        return $name !== '' ? $name : $category->name;
    }

    /**
     * @param  \Illuminate\Support\Collection<int, Product>  $products
     */
    private static function findMatchingProduct($products, string $needle): bool
    {
        $needleSlug = str($needle)->lower()->replace('+', '-')->replace('_', '-')->slug()->toString();
        $needleLower = Str::lower($needle);

        return $products->contains(
            fn (Product $product): bool => Str::contains(Str::lower($product->name), $needleLower)
                || Str::contains(Str::lower((string) $product->sku), $needleLower)
                || Str::contains(Str::lower((string) $product->slug), $needleSlug)
        );
    }

    /**
     * @return array<string, string>
     */
    public static function legacyCategoryRedirects(): array
    {
        return [
            'solar-flood-lights' => self::PRICE_AUTHORITY_SLUG,
            'solar-floodlights' => self::PRICE_AUTHORITY_SLUG,
            'solar-flood-light-price-in-kenya' => self::PRICE_AUTHORITY_SLUG,
            'solar-flood-lights-for-sale-in-kenya' => self::PRICE_AUTHORITY_SLUG,
            'led-solar-flood-lights' => self::PRICE_AUTHORITY_SLUG,
            'outdoor-security-lights' => 'solar-security-lights',
            'solar-security-light' => 'solar-security-lights',
            'motion-sensor-flood-lights' => 'motion-sensor-solar-lights',
            'solar-streetlights' => 'solar-street-lights',
            'solar-wall-lights' => 'solar-garden-wall-lights',
            'solar-garden-lights' => 'solar-garden-wall-lights',
            'solar-batteries' => 'solar-panels-batteries',
            'solar-light-accessories' => 'solar-lighting-accessories',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function topLevelCategoryRedirects(): array
    {
        return [
            'solar-flood-lights' => self::PRICE_AUTHORITY_SLUG,
            'outdoor-solar-flood-lights' => 'outdoor-solar-flood-lights',
            'motion-sensor-solar-lights' => 'motion-sensor-solar-lights',
            'solar-street-lights' => 'solar-street-lights',
            'solar-security-lights' => 'solar-security-lights',
            'solar-garden-wall-lights' => 'solar-garden-wall-lights',
            'solar-panels-batteries' => 'solar-panels-batteries',
            'solar-lighting-accessories' => 'solar-lighting-accessories',
        ];
    }

    public static function isPriceAuthorityCategory(?Category $category): bool
    {
        if (! $category) {
            return false;
        }

        return in_array($category->slug, array_merge(
            [self::PRICE_AUTHORITY_SLUG],
            array_keys(array_filter(
                self::legacyCategoryRedirects(),
                fn (string $target): bool => $target === self::PRICE_AUTHORITY_SLUG
            ))
        ), true);
    }

    public static function isRouterAuthorityCategory(?Category $category): bool
    {
        return self::isPriceAuthorityCategory($category);
    }

    public static function isBroadSolarCategory(?Category $category): bool
    {
        if (! $category) {
            return false;
        }

        return in_array(Str::slug($category->slug), [
            'solar',
            'solar-lights',
            'solar-lighting',
            'solar-products',
            'solar-products-in-kenya',
            'solar-flood-lights-kenya',
        ], true);
    }

    public static function isBroadMikrotikCategory(?Category $category): bool
    {
        return self::isBroadSolarCategory($category);
    }

    public static function targetSlugForLegacy(string $slug): ?string
    {
        return self::legacyCategoryRedirects()[Str::slug($slug)] ?? null;
    }

    public static function targetSlugForTopLevel(string $slug): ?string
    {
        return self::topLevelCategoryRedirects()[Str::slug($slug)] ?? null;
    }

    public static function productIntentSlug(Product $product): ?string
    {
        $text = Str::lower(implode(' ', [
            $product->name,
            $product->slug,
            $product->sku,
            $product->category?->name,
            $product->category?->slug,
        ]));

        if (Str::contains($text, ['pole', 'bracket', 'mount', 'mounting', 'cable', 'remote', 'accessory', 'accessories'])) {
            return 'solar-lighting-accessories';
        }

        if (Str::contains($text, ['panel', 'battery', 'lithium', 'lifepo4', 'charge controller'])) {
            return 'solar-panels-batteries';
        }

        if (Str::contains($text, ['street light', 'streetlight', 'road light', 'estate light', 'all-in-one'])) {
            return 'solar-street-lights';
        }

        if (Str::contains($text, ['garden', 'wall', 'pathway', 'patio'])) {
            return 'solar-garden-wall-lights';
        }

        if (Str::contains($text, ['motion', 'sensor', 'pir'])) {
            return 'motion-sensor-solar-lights';
        }

        if (Str::contains($text, ['security', 'perimeter', 'cctv', 'high mast', 'commercial'])) {
            return 'solar-security-lights';
        }

        if (Str::contains($text, ['outdoor', 'yard', 'compound', 'parking', 'farm'])) {
            return 'outdoor-solar-flood-lights';
        }

        if (Str::contains($text, ['flood', 'floodlight', 'flood light', 'solar light', 'solar'])) {
            return self::PRICE_AUTHORITY_SLUG;
        }

        return null;
    }

    public static function solarProductsQuery(): Builder
    {
        return Product::query()
            ->with(['vendor', 'category', 'images' => fn ($query) => $query->orderByDesc('is_primary')->orderBy('sort_order')])
            ->active()
            ->where(function (Builder $query): void {
                $query->where('name', 'like', '%solar%')
                    ->orWhere('name', 'like', '%flood%')
                    ->orWhere('name', 'like', '%light%')
                    ->orWhere('slug', 'like', '%solar%')
                    ->orWhere('slug', 'like', '%flood%')
                    ->orWhere('description', 'like', '%solar%')
                    ->orWhere('description', 'like', '%flood%')
                    ->orWhereHas('category', function (Builder $categoryQuery): void {
                        $categoryQuery->where('name', 'like', '%solar%')
                            ->orWhere('name', 'like', '%flood%')
                            ->orWhere('name', 'like', '%light%')
                            ->orWhere('slug', 'like', '%solar%')
                            ->orWhere('slug', 'like', '%flood%');
                    });
            });
    }

    public static function mikrotikProductsQuery(): Builder
    {
        return self::solarProductsQuery();
    }

    /**
     * @return array<int, array{question: string, answer: string}>
     */
    public static function priceFaqItems(): array
    {
        return [
            [
                'question' => 'Which solar flood light wattage is best for a Kenyan home compound?',
                'answer' => 'Most home compounds use 60W to 200W lights depending on the mounting height, area size and brightness needed. Larger yards, farms and commercial spaces may need multiple units or higher wattage.',
            ],
            [
                'question' => 'Do solar flood light prices include the panel and battery?',
                'answer' => 'Product pages should state what is included. Many solar flood lights include a panel, battery, remote and mounting hardware, but confirm the package before checkout or quotation approval.',
            ],
            [
                'question' => 'Can solar flood lights work through rainy seasons?',
                'answer' => 'A good installation should match the panel, battery capacity and lighting mode to expected runtime. Confirm charging time, battery capacity and weather rating before buying for critical security lighting.',
            ],
        ];
    }

    public static function routerFaqItems(): array
    {
        return self::priceFaqItems();
    }
}
