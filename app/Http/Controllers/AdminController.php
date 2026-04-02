<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AdminController extends Controller
{
    private function uniqueSlug(string $table, string $text): string
    {
        $base = Str::slug($text);
        if ($base === '') {
            $base = Str::lower(Str::random(8));
        }

        $slug = $base;
        $counter = 1;
        while (DB::table($table)->where('slug', $slug)->exists()) {
            $slug = $base . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    private function nextSku(): string
    {
        $sku = strtoupper('SKU-' . Str::upper(Str::random(8)));

        while (Product::where('sku', $sku)->exists()) {
            $sku = strtoupper('SKU-' . Str::upper(Str::random(8)));
        }

        return $sku;
    }

    private function adminVendor(Request $request, bool $create = false): ?Vendor
    {
        $user = $request->user();
        if (!$user) {
            return null;
        }

        $vendor = Vendor::query()->where('user_id', $user->id)->first();
        if ($vendor) {
            if (!$vendor->is_approved) {
                $vendor->update(['is_approved' => true]);
                $vendor->refresh();
            }

            return $vendor;
        }

        if (!$create) {
            return null;
        }

        return Vendor::create([
            'user_id' => $user->id,
            'shop_name' => config('app.name', 'Almar Market') . ' Official Store',
            'slug' => $this->uniqueSlug('vendors', config('app.name', 'Almar Market') . ' Official Store'),
            'description' => 'Products managed by the marketplace admin.',
            'phone' => $user->phone ?: 'Admin desk',
            'address' => 'Platform managed catalog',
            'is_approved' => true,
        ]);
    }

    public function dashboard(Request $request): View
    {
        $adminVendor = $this->adminVendor($request);
        $stats = [
            'total_users' => User::count(),
            'total_vendors' => Vendor::count(),
            'pending_vendors' => Vendor::where('is_approved', false)->count(),
            'approved_vendors' => Vendor::where('is_approved', true)->count(),
            'total_products' => Product::count(),
            'active_products' => Product::where('status', 'active')->count(),
            'total_orders' => Order::count(),
            'pending_orders' => Order::where('status', 'pending')->count(),
            'gross_revenue' => (float) Order::sum('total_amount'),
        ];

        $recentOrders = Order::query()
            ->with('user')
            ->latest()
            ->limit(8)
            ->get();

        $pendingVendors = Vendor::query()
            ->with('user')
            ->where('is_approved', false)
            ->latest()
            ->limit(8)
            ->get();

        $adminProducts = $adminVendor
            ? $adminVendor->products()->with('category')->latest()->limit(12)->get()
            : collect();

        return view('admin.dashboard', [
            'stats' => $stats,
            'recentOrders' => $recentOrders,
            'pendingVendors' => $pendingVendors,
            'categories' => Category::query()->orderBy('name')->get(),
            'adminVendor' => $adminVendor,
            'adminProducts' => $adminProducts,
        ]);
    }

    public function pendingVendors(): View
    {
        return view('admin.vendors', [
            'vendors' => Vendor::query()
                ->with('user')
                ->where('is_approved', false)
                ->latest()
                ->get(),
        ]);
    }

    public function storeProduct(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:180'],
            'category_id' => ['required', 'exists:categories,id'],
            'description' => ['nullable', 'string', 'max:5000'],
            'price' => ['required', 'numeric', 'min:0.01'],
            'stock' => ['required', 'integer', 'min:0'],
            'image_url' => ['nullable', 'url', 'max:255'],
        ]);

        $vendor = $this->adminVendor($request, true);
        if (!$vendor) {
            return redirect()->route('admin.dashboard')->with('error', 'Unable to initialize the admin store.');
        }

        $product = Product::create([
            'vendor_id' => $vendor->id,
            'category_id' => $data['category_id'],
            'name' => $data['name'],
            'slug' => $this->uniqueSlug('products', $data['name']),
            'description' => $data['description'] ?? null,
            'price' => $data['price'],
            'stock' => $data['stock'],
            'sku' => $this->nextSku(),
            'status' => 'active',
        ]);

        if (!empty($data['image_url'])) {
            ProductImage::create([
                'product_id' => $product->id,
                'image_url' => $data['image_url'],
                'is_primary' => true,
                'sort_order' => 0,
            ]);
        }

        return redirect()->route('admin.dashboard')->with('success', 'Product added to the admin catalog.');
    }

    public function approveVendor(Request $request, Vendor $vendor): RedirectResponse
    {
        $vendor->update(['is_approved' => true]);
        $vendor->products()->where('status', 'draft')->update(['status' => 'active']);
        $vendor->user()->update(['role' => 'vendor']);

        return back()->with('success', 'Vendor approved successfully.');
    }
}
