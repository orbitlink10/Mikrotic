<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StorefrontController extends Controller
{
    public function index(Request $request): View
    {
        $categories = Category::query()->orderBy('name')->get();
        $search = trim((string) $request->query('search', ''));
        $categoryId = $request->integer('category') ?: null;

        $productsQuery = Product::query()
            ->with(['vendor', 'category', 'images' => fn ($query) => $query->orderByDesc('is_primary')->orderBy('sort_order')])
            ->active();

        if ($categoryId) {
            $productsQuery->where('category_id', $categoryId);
        }

        if ($search !== '') {
            $productsQuery->where(function ($query) use ($search): void {
                $query->where('name', 'like', '%' . $search . '%')
                    ->orWhere('description', 'like', '%' . $search . '%');
            });
        }

        $products = $productsQuery->latest()->paginate(24)->withQueryString();

        return view('home', [
            'categories' => $categories,
            'products' => $products,
            'search' => $search,
            'selectedCategory' => $categoryId,
        ]);
    }

    public function show(Product $product): View
    {
        $product->load(['vendor', 'category', 'images' => fn ($query) => $query->orderByDesc('is_primary')->orderBy('sort_order')]);

        if ($product->status !== 'active' || !$product->vendor?->is_approved) {
            abort(404);
        }

        return view('product.show', ['product' => $product]);
    }
}
