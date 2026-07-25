<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\HomepageContent;
use App\Models\Page;
use App\Models\Product;
use App\Models\Testimonial;
use App\Support\ProductContent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class StorefrontController extends Controller
{
    private const ROUTER_PRICES_CATEGORY_NAME = 'Mikrotik Router Prices in Kenya';
    private const ROUTER_PRICES_CATEGORY_SLUG = 'mikrotik-router-prices-in-kenya';
    private const ROUTER_PRODUCTS_LIMIT = 8;

    public function index(Request $request): View|RedirectResponse
    {
        $category = $request->filled('category')
            ? Category::query()->find($request->integer('category'))
            : null;

        return $this->renderCatalog($request, $category);
    }

    public function showCategory(Request $request, Category $category): View|RedirectResponse
    {
        return $this->renderCatalog($request, $category);
    }

    public function show(Product $product): View
    {
        $product->load(['vendor', 'category', 'images' => fn ($query) => $query->orderByDesc('is_primary')->orderBy('sort_order')]);

        if ($product->status !== 'active' || !$product->vendor?->is_approved) {
            abort(404);
        }

        return view('product.show', ['product' => $product]);
    }

    public function showPage(Page $page): View
    {
        return view('page.show', [
            'page' => $page,
            'pageBody' => ProductContent::sanitizeRichText($page->body) ?: '<p>No content available.</p>',
            'pageMetaDescription' => $page->meta_description ?: ProductContent::excerpt($page->body, 160),
        ]);
    }

    private function renderCatalog(Request $request, ?Category $currentCategory = null): View|RedirectResponse
    {
        $categories = Category::query()
            ->whereNull('parent_id')
            ->orderBy('name')
            ->get();

        $search = trim((string) $request->query('search', ''));
        $searchSlug = Str::slug($search);
        $normalizedSearch = Str::lower($search);
        $productsQuery = Product::query()
            ->with(['vendor', 'category', 'images' => fn ($query) => $query->orderByDesc('is_primary')->orderBy('sort_order')])
            ->active();

        $selectedCategory = null;
        if ($currentCategory) {
            $currentCategory->loadMissing('children', 'parent');
            $selectedCategory = $currentCategory->parent_id ?: $currentCategory->id;
            $productsQuery->whereIn('category_id', $this->catalogCategoryIds($currentCategory));
        }

        if ($search !== '' && !$currentCategory) {
            $exactProduct = Product::query()
                ->active()
                ->where(function ($query) use ($normalizedSearch, $searchSlug): void {
                    $query->whereRaw('LOWER(name) = ?', [$normalizedSearch])
                        ->orWhereRaw('LOWER(sku) = ?', [$normalizedSearch]);

                    if ($searchSlug !== '') {
                        $query->orWhere('slug', $searchSlug);
                    }
                })
                ->first();

            if ($exactProduct) {
                return redirect()->route('product.show', $exactProduct);
            }
        }

        if ($search !== '') {
            $productsQuery->where(function ($query) use ($search, $searchSlug): void {
                $query->where('name', 'like', '%' . $search . '%')
                    ->orWhere('sku', 'like', '%' . $search . '%')
                    ->orWhere('description', 'like', '%' . $search . '%')
                    ->orWhere('meta_description', 'like', '%' . $search . '%')
                    ->orWhereHas('category', fn ($categoryQuery) => $categoryQuery->where('name', 'like', '%' . $search . '%'))
                    ->orWhereHas('vendor', fn ($vendorQuery) => $vendorQuery->where('shop_name', 'like', '%' . $search . '%'));

                if ($searchSlug !== '') {
                    $query->orWhere('slug', 'like', '%' . $searchSlug . '%');
                }
            });
        }

        $homepageProductCategory = $search === '' && !$currentCategory
            ? $this->routerPricesCategory()
            : null;
        $homepageRouterProducts = $homepageProductCategory
            ? Product::query()
                ->with(['vendor', 'category', 'images' => fn ($query) => $query->orderByDesc('is_primary')->orderBy('sort_order')])
                ->active()
                ->whereIn('category_id', $this->catalogCategoryIds($homepageProductCategory))
                ->latest()
                ->limit(self::ROUTER_PRODUCTS_LIMIT)
                ->get()
            : collect();

        if ($homepageRouterProducts->isNotEmpty()) {
            $productsQuery->whereNotIn('id', $homepageRouterProducts->modelKeys());
        }

        $products = $productsQuery->latest()->paginate(24)->withQueryString();

        return view('home', [
            'categories' => $categories,
            'homepageContent' => HomepageContent::current(),
            'homepageProductCategory' => $homepageProductCategory,
            'homepageRouterProducts' => $homepageRouterProducts,
            'products' => $products,
            'search' => $search,
            'selectedCategory' => $selectedCategory,
            'testimonials' => Testimonial::homepageItems(),
            'currentCategory' => $currentCategory,
        ]);
    }

    private function routerPricesCategory(): ?Category
    {
        return Category::query()
            ->where(function ($query): void {
                $query->whereRaw('LOWER(name) = ?', [Str::lower(self::ROUTER_PRICES_CATEGORY_NAME)])
                    ->orWhere('slug', self::ROUTER_PRICES_CATEGORY_SLUG);
            })
            ->with('children')
            ->first();
    }

    /**
     * @return array<int>
     */
    private function catalogCategoryIds(Category $category): array
    {
        if ($category->parent_id) {
            return [$category->id];
        }

        $category->loadMissing('children');

        return array_merge([$category->id], $category->children->pluck('id')->all());
    }
}
