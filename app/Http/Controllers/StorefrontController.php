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
            $categoryIds = $currentCategory->parent_id
                ? [$currentCategory->id]
                : array_merge([$currentCategory->id], $currentCategory->children->pluck('id')->all());

            $productsQuery->whereIn('category_id', $categoryIds);
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

        $products = $productsQuery->latest()->paginate(24)->withQueryString();

        return view('home', [
            'categories' => $categories,
            'homepageContent' => HomepageContent::current(),
            'products' => $products,
            'search' => $search,
            'selectedCategory' => $selectedCategory,
            'testimonials' => Testimonial::homepageItems(),
            'currentCategory' => $currentCategory,
        ]);
    }
}
