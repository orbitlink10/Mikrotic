<?php

namespace App\Support;

use App\Models\HomepageContent;
use App\Models\Product;

class StructuredData
{
    /**
     * @param  array<int, string>  $images
     */
    public static function product(Product $product, array $images, string $description, string $canonicalUrl): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'Product',
            'name' => $product->name,
            'image' => array_values(array_filter($images)),
            'description' => $description,
            'sku' => $product->sku,
            'mpn' => ProductSeo::model($product),
            'brand' => [
                '@type' => 'Brand',
                'name' => ProductSeo::brand($product),
            ],
            'offers' => [
                '@type' => 'Offer',
                'url' => $canonicalUrl,
                'priceCurrency' => 'KES',
                'price' => number_format((float) $product->price, 2, '.', ''),
                'availability' => $product->stock > 0
                    ? 'https://schema.org/InStock'
                    : 'https://schema.org/OutOfStock',
                'seller' => [
                    '@type' => 'Organization',
                    'name' => config('business.name', config('app.name', 'Mikrotik Kenya')),
                ],
            ],
        ];
    }

    /**
     * @param  array<int, array{name: string, url: string}>  $items
     */
    public static function breadcrumbs(array $items): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => array_map(
                fn (array $item, int $index): array => [
                    '@type' => 'ListItem',
                    'position' => $index + 1,
                    'name' => $item['name'],
                    'item' => $item['url'],
                ],
                array_values($items),
                array_keys(array_values($items))
            ),
        ];
    }

    /**
     * @param  array<int, array{question: string, answer: string}>  $items
     */
    public static function faq(array $items): ?array
    {
        $items = array_values(array_filter($items, fn (array $item): bool => $item['question'] !== '' && $item['answer'] !== ''));
        if ($items === []) {
            return null;
        }

        return [
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => array_map(fn (array $item): array => [
                '@type' => 'Question',
                'name' => $item['question'],
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => $item['answer'],
                ],
            ], $items),
        ];
    }

    public static function organization(?HomepageContent $homepageContent = null): array
    {
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            'name' => config('business.name', config('app.name', 'Mikrotik Kenya')),
            'url' => CanonicalUrl::normalize('/'),
        ];

        if (config('business.legal_name')) {
            $schema['legalName'] = config('business.legal_name');
        }

        if ($homepageContent?->siteLogoUrl()) {
            $schema['logo'] = CanonicalUrl::absoluteAsset($homepageContent->siteLogoUrl());
        }

        if (config('business.phone')) {
            $schema['telephone'] = config('business.phone');
        }

        if (config('business.email')) {
            $schema['email'] = config('business.email');
        }

        if (config('business.address')) {
            $schema['address'] = [
                '@type' => 'PostalAddress',
                'streetAddress' => config('business.address'),
            ];
        }

        if (config('business.social_profiles')) {
            $schema['sameAs'] = config('business.social_profiles');
        }

        return $schema;
    }
}
