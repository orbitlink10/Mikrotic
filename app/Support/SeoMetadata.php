<?php

namespace App\Support;

use App\Models\Category;
use App\Models\Page;
use App\Models\Product;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class SeoMetadata
{
    public static function homepageTitle(int $page = 1): string
    {
        $title = 'MikroTik Kenya | Routers, Switches & Networking Equipment';

        return $page > 1 ? $title.' - Page '.$page : $title;
    }

    public static function homepageDescription(): string
    {
        return 'Shop genuine MikroTik routers, switches, access points and networking equipment in Kenya. Compare prices, specifications and availability with fast delivery across Kenya.';
    }

    public static function categoryTitle(Category $category, int $page = 1): string
    {
        $categoryName = $category->name;
        $mappedTitle = MikrotikSeoCatalog::categoryTitles()[Str::slug($category->slug)] ?? null;
        $categoryTitle = $mappedTitle
            ?: (Str::contains(Str::lower($categoryName), ['kenya', 'price'])
                ? $categoryName.' | MikroTik Kenya'
                : $categoryName.' in Kenya | MikroTik Kenya');

        $title = self::columnValue($category, 'seo_title')
            ?: (MikrotikSeoCatalog::isRouterAuthorityCategory($category)
                ? MikrotikSeoCatalog::categoryTitles()[MikrotikSeoCatalog::ROUTER_AUTHORITY_SLUG]
                : $categoryTitle);

        return $page > 1 ? $title.' - Page '.$page : $title;
    }

    public static function categoryDescription(Category $category): string
    {
        return self::cleanDescription(
            self::columnValue($category, 'meta_description')
                ?: self::columnValue($category, 'intro')
                ?: ProductContent::excerpt((string) self::columnValue($category, 'description'), 155)
                ?: 'Shop '.$category->name.' in Kenya with current prices, product availability and delivery options.'
        );
    }

    public static function productTitle(Product $product): string
    {
        if ($customTitle = self::columnValue($product, 'seo_title')) {
            return $customTitle;
        }

        $model = ProductSeo::model($product);
        $typeLabel = ProductSeo::typeLabel($product);

        return Str::limit($model.' Price in Kenya | MikroTik '.$typeLabel, 78, '');
    }

    public static function productDescription(Product $product): string
    {
        return self::cleanDescription(
            self::columnValue($product, 'meta_description')
                ?: ProductContent::excerpt($product->description, 155)
                ?: 'Buy '.$product->name.' in Kenya. View current price, specifications, availability and delivery options.'
        );
    }

    public static function pageTitle(Page $page): string
    {
        return self::columnValue($page, 'seo_title')
            ?: self::columnValue($page, 'meta_title')
            ?: $page->title;
    }

    public static function pageDescription(Page $page, ?string $fallback = null): string
    {
        return self::cleanDescription(
            self::columnValue($page, 'meta_description')
                ?: $fallback
                ?: ProductContent::excerpt($page->body, 155)
                ?: $page->title
        );
    }

    public static function canonicalOverride(object $model): ?string
    {
        return self::columnValue($model, 'canonical_url');
    }

    public static function robots(object $model): ?string
    {
        return self::columnValue($model, 'robots');
    }

    public static function openGraphTitle(object $model, string $fallback): string
    {
        return self::columnValue($model, 'og_title') ?: $fallback;
    }

    public static function openGraphDescription(object $model, string $fallback): string
    {
        return self::cleanDescription(self::columnValue($model, 'og_description') ?: $fallback);
    }

    public static function openGraphImage(object $model, ?string $fallback = null): ?string
    {
        return self::columnValue($model, 'og_image') ?: $fallback;
    }

    public static function columnReady(string $table, string $column): bool
    {
        static $cache = [];
        $key = $table.'.'.$column;

        return $cache[$key] ??= Schema::hasTable($table) && Schema::hasColumn($table, $column);
    }

    private static function columnValue(object $model, string $column): ?string
    {
        if (! method_exists($model, 'getTable') || ! self::columnReady($model->getTable(), $column)) {
            return null;
        }

        $value = trim((string) ($model->{$column} ?? ''));

        return $value !== '' ? $value : null;
    }

    private static function cleanDescription(string $description): string
    {
        return Str::limit(trim(preg_replace('/\s+/u', ' ', strip_tags($description)) ?? ''), 160, '');
    }
}
