<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Page;
use App\Models\Product;
use App\Support\CanonicalUrl;
use App\Support\SeoMetadata;
use App\Support\SolarFloodLightSeoCatalog;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function __invoke(): Response
    {
        $urls = collect([
            [
                'loc' => CanonicalUrl::route('home'),
                'lastmod' => now()->toDateString(),
                'priority' => '1.0',
            ],
        ]);

        $categoryUrls = Category::query()
            ->whereNotIn('slug', array_keys(SolarFloodLightSeoCatalog::legacyCategoryRedirects()))
            ->when(SeoMetadata::columnReady('categories', 'robots'), fn ($query) => $query->where(fn ($robotsQuery) => $robotsQuery->whereNull('robots')->orWhere('robots', 'not like', 'noindex%')))
            ->orderBy('updated_at', 'desc')
            ->get()
            ->map(fn (Category $category): array => [
                'loc' => CanonicalUrl::route('category.show', $category),
                'lastmod' => optional($category->updated_at)->toDateString(),
                'priority' => '0.8',
            ]);

        $productUrls = Product::query()
            ->active()
            ->when(SeoMetadata::columnReady('products', 'robots'), fn ($query) => $query->where(fn ($robotsQuery) => $robotsQuery->whereNull('robots')->orWhere('robots', 'not like', 'noindex%')))
            ->orderBy('updated_at', 'desc')
            ->get()
            ->map(fn (Product $product): array => [
                'loc' => CanonicalUrl::route('product.show', $product),
                'lastmod' => optional($product->updated_at)->toDateString(),
                'priority' => '0.7',
            ]);

        $pageUrls = Page::query()
            ->when(SeoMetadata::columnReady('pages', 'robots'), fn ($query) => $query->where(fn ($robotsQuery) => $robotsQuery->whereNull('robots')->orWhere('robots', 'not like', 'noindex%')))
            ->orderBy('updated_at', 'desc')
            ->get()
            ->map(fn (Page $page): array => [
                'loc' => CanonicalUrl::route('pages.show', $page),
                'lastmod' => optional($page->updated_at)->toDateString(),
                'priority' => '0.6',
            ]);

        $comparisonUrls = collect(SolarFloodLightSeoCatalog::resolvableComparisonSlugs())
            ->map(fn (string $slug): array => [
                'loc' => CanonicalUrl::route('comparison.show', $slug),
                'lastmod' => null,
                'priority' => '0.6',
            ]);

        foreach (['about-us', 'contact-us', 'delivery-policy', 'returns-policy', 'warranty-policy', 'privacy-policy', 'terms-and-conditions'] as $slug) {
            if (! $pageUrls->contains('loc', CanonicalUrl::route('pages.show', ['page' => $slug]))) {
                $pageUrls->push([
                    'loc' => CanonicalUrl::route('pages.show', ['page' => $slug]),
                    'lastmod' => null,
                    'priority' => '0.4',
                ]);
            }
        }

        $urls = $urls->merge($categoryUrls)->merge($productUrls)->merge($pageUrls)->merge($comparisonUrls);

        $xml = view('sitemap', ['urls' => $urls])->render();

        return response($xml, 200)->header('Content-Type', 'application/xml');
    }
}
