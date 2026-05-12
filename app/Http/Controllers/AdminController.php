<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\HomepageContent;
use App\Models\Order;
use App\Models\Page;
use App\Models\Product;
use App\Models\ProductImage;
use App\Support\ProductContent;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
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

    private function resolveCategory(array $data): Category
    {
        if (!empty($data['subcategory_id'])) {
            return Category::query()->findOrFail($data['subcategory_id']);
        }

        if (!empty($data['category_id'])) {
            return Category::query()->findOrFail($data['category_id']);
        }

        $name = trim((string) ($data['category_name'] ?? ''));
        $existingCategory = Category::query()
            ->whereRaw('LOWER(name) = ?', [Str::lower($name)])
            ->first();

        if ($existingCategory) {
            return $existingCategory;
        }

        return Category::create([
            'name' => $name,
            'slug' => $this->uniqueSlug('categories', $name),
        ]);
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
            'new_users_30_days' => User::query()->where('created_at', '>=', now()->subDays(30))->count(),
            'active_users_24_hours' => DB::table('sessions')
                ->whereNotNull('user_id')
                ->where('last_activity', '>=', now()->subDay()->getTimestamp())
                ->distinct()
                ->count('user_id'),
            'total_vendors' => Vendor::count(),
            'pending_vendors' => Vendor::where('is_approved', false)->count(),
            'approved_vendors' => Vendor::where('is_approved', true)->count(),
            'total_products' => Product::count(),
            'active_products' => Product::where('status', 'active')->count(),
            'total_orders' => Order::count(),
            'pending_orders' => Order::where('status', 'pending')->count(),
            'recent_orders_7_days' => Order::query()->where('created_at', '>=', now()->subDays(7))->count(),
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

    public function categoriesIndex(): View
    {
        return view('admin.categories_index', [
            'categories' => Category::query()
                ->whereNull('parent_id')
                ->withCount('products')
                ->latest()
                ->get(),
        ]);
    }

    public function subcategoriesIndex(): View
    {
        return view('admin.subcategories_index', [
            'subcategories' => Category::query()
                ->whereNotNull('parent_id')
                ->with(['parent'])
                ->withCount('products')
                ->latest()
                ->get(),
        ]);
    }

    public function createCategoryForm(Request $request): View
    {
        $defaultParentId = $request->integer('parent_id');

        return view('admin.category_create', [
            'parents' => Category::query()
                ->whereNull('parent_id')
                ->orderBy('name')
                ->get(),
            'defaultParentId' => $defaultParentId > 0 ? $defaultParentId : null,
        ]);
    }

    public function storeCategory(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:120', 'unique:categories,name'],
            'meta_description' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'parent_id' => ['nullable', 'exists:categories,id'],
            'image_url' => ['nullable', 'url', 'max:255'],
        ]);

        $category = Category::create([
            'name' => $data['name'],
            'meta_description' => ProductContent::sanitizeMetaDescription($data['meta_description'] ?? null),
            'slug' => $this->uniqueSlug('categories', $data['name']),
            'parent_id' => $data['parent_id'] ?? null,
            'image_url' => $data['image_url'] ?? null,
            'description' => ProductContent::sanitizeRichText($data['description'] ?? null),
        ]);

        $redirectRoute = $category->parent_id ? 'admin.subcategories.index' : 'admin.categories.index';

        return redirect()->route($redirectRoute)->with('success', 'Category saved successfully.');
    }

    public function productsIndex(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));

        $products = Product::query()
            ->with([
                'category',
                'images' => fn ($query) => $query->orderByDesc('is_primary')->orderBy('sort_order'),
            ])
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($builder) use ($search): void {
                    $builder->where('name', 'like', '%' . $search . '%')
                        ->orWhere('slug', 'like', '%' . $search . '%')
                        ->orWhere('sku', 'like', '%' . $search . '%');
                });
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.products_index', [
            'products' => $products,
            'search' => $search,
        ]);
    }

    public function ordersIndex(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));
        $status = trim((string) $request->query('status', ''));

        $orders = Order::query()
            ->with('user')
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($builder) use ($search): void {
                    $builder->where('order_number', 'like', '%' . $search . '%')
                        ->orWhere('shipping_name', 'like', '%' . $search . '%')
                        ->orWhere('shipping_email', 'like', '%' . $search . '%')
                        ->orWhereHas('user', fn ($userQuery) => $userQuery->where('name', 'like', '%' . $search . '%'));
                });
            })
            ->when($status !== '', fn ($query) => $query->where('status', $status))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.orders_index', [
            'orders' => $orders,
            'search' => $search,
            'status' => $status,
            'statuses' => ['pending', 'paid', 'processing', 'shipped', 'delivered', 'cancelled'],
        ]);
    }

    public function pagesIndex(): View
    {
        $pagesStorageReady = Page::storageReady();

        return view('admin.pages_index', [
            'pages' => $pagesStorageReady
                ? Page::query()->latest()->paginate(20)
                : new LengthAwarePaginator([], 0, 20),
            'pagesStorageReady' => $pagesStorageReady,
        ]);
    }

    public function homepageContentForm(): View
    {
        return view('admin.homepage_content', [
            'homepageContent' => HomepageContent::current(),
            'homepageContentStorageReady' => HomepageContent::storageReady(),
        ]);
    }

    public function updateHomepageContent(Request $request): RedirectResponse
    {
        if (!HomepageContent::storageReady()) {
            return redirect()
                ->route('admin.pages-content.edit')
                ->with('error', 'Homepage content storage is not ready yet. Run php artisan migrate to create the homepage_contents table.');
        }

        $data = $request->validate([
            'hero_title' => ['required', 'string', 'min:4', 'max:180'],
            'hero_description' => ['required', 'string', 'min:12', 'max:500'],
            'hero_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);

        $homepageContent = HomepageContent::query()->firstOrNew([
            'site_key' => HomepageContent::DEFAULT_SITE_KEY,
        ]);

        $homepageContent->hero_title = $data['hero_title'];
        $homepageContent->hero_description = $data['hero_description'];

        if ($request->hasFile('hero_image')) {
            $directory = public_path('uploads/homepage-content');
            File::ensureDirectoryExists($directory);

            if ($homepageContent->hero_image_path && File::exists(public_path($homepageContent->hero_image_path))) {
                File::delete(public_path($homepageContent->hero_image_path));
            }

            $image = $request->file('hero_image');
            $filename = now()->format('YmdHis') . '-' . Str::lower(Str::random(10)) . '.' . $image->getClientOriginalExtension();
            $image->move($directory, $filename);

            $homepageContent->hero_image_path = 'uploads/homepage-content/' . $filename;
        }

        $homepageContent->save();

        return redirect()->route('admin.pages-content.edit')->with('success', 'Homepage content updated successfully.');
    }

    public function createPageForm(): View
    {
        return view('admin.page_create', [
            'pagesStorageReady' => Page::storageReady(),
        ]);
    }

    public function storePage(Request $request): RedirectResponse
    {
        if (!Page::storageReady()) {
            return redirect()
                ->route('admin.pages.index')
                ->with('error', 'Page storage is not ready yet. Run php artisan migrate to create the pages table.');
        }

        $data = $request->validate([
            'meta_title' => ['required', 'string', 'min:2', 'max:180'],
            'meta_description' => ['required', 'string', 'min:10', 'max:255'],
            'title' => ['required', 'string', 'min:2', 'max:180'],
            'slug' => ['nullable', 'string', 'max:180', 'unique:pages,slug'],
            'image_url' => ['nullable', 'url', 'max:255'],
            'alt_text' => ['nullable', 'string', 'min:2', 'max:255', 'required_with:image_url'],
            'heading_two' => ['required', 'string', 'min:2', 'max:180'],
            'type' => ['required', 'in:page,post'],
            'body' => ['required', 'string'],
        ]);

        Page::create([
            'meta_title' => Str::limit(trim(strip_tags($data['meta_title'])), 180, ''),
            'meta_description' => ProductContent::sanitizeMetaDescription($data['meta_description']),
            'title' => trim($data['title']),
            'heading_two' => Str::limit(trim(strip_tags($data['heading_two'])), 180, ''),
            'slug' => !empty($data['slug']) ? Str::slug($data['slug']) : $this->uniqueSlug('pages', $data['title']),
            'image_url' => $data['image_url'] ?? null,
            'alt_text' => !empty($data['alt_text']) ? trim($data['alt_text']) : null,
            'type' => $data['type'],
            'body' => ProductContent::sanitizeRichText($data['body']),
        ]);

        return redirect()->route('admin.pages.index')->with('success', 'Page saved successfully.');
    }

    public function invoicesIndex(): View
    {
        return view('admin.invoices_index', [
            'orders' => Order::query()
                ->with('user')
                ->latest()
                ->paginate(20),
        ]);
    }

    public function createProductForm(): View
    {
        return view('admin.product_create', [
            'categories' => Category::query()
                ->whereNull('parent_id')
                ->with(['children' => fn ($query) => $query->orderBy('name')])
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function storeProduct(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:180'],
            'category_id' => ['nullable', 'exists:categories,id', 'required_without:category_name'],
            'category_name' => ['nullable', 'string', 'min:2', 'max:120', 'required_without:category_id'],
            'subcategory_id' => ['nullable', 'exists:categories,id'],
            'description' => ['nullable', 'string', 'max:5000'],
            'meta_description' => ['nullable', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0.01'],
            'compare_at_price' => ['nullable', 'numeric', 'gte:price'],
            'stock' => ['required', 'integer', 'min:0'],
            'image_url' => ['nullable', 'url', 'max:255'],
        ]);

        if (!empty($data['subcategory_id'])) {
            $subcategory = Category::query()
                ->whereKey($data['subcategory_id'])
                ->whereNotNull('parent_id')
                ->first();

            if (!$subcategory) {
                throw ValidationException::withMessages([
                    'subcategory_id' => 'Select a valid subcategory.',
                ]);
            }

            if (!empty($data['category_id']) && $subcategory->parent_id !== (int) $data['category_id']) {
                throw ValidationException::withMessages([
                    'subcategory_id' => 'Select a subcategory that belongs to the chosen category.',
                ]);
            }
        }

        $vendor = $this->adminVendor($request, true);
        if (!$vendor) {
            return redirect()->route('admin.dashboard')->with('error', 'Unable to initialize the admin store.');
        }

        $category = $this->resolveCategory($data);

        $product = Product::create([
            'vendor_id' => $vendor->id,
            'category_id' => $category->id,
            'name' => $data['name'],
            'slug' => $this->uniqueSlug('products', $data['name']),
            'description' => ProductContent::sanitizeRichText($data['description'] ?? null),
            'meta_description' => ProductContent::sanitizeMetaDescription($data['meta_description'] ?? null),
            'price' => $data['price'],
            'compare_at_price' => $data['compare_at_price'] ?? null,
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

        return redirect()->route('admin.products.index')->with('success', 'Product added to the admin catalog.');
    }

    public function approveVendor(Request $request, Vendor $vendor): RedirectResponse
    {
        $vendor->update(['is_approved' => true]);
        $vendor->products()->where('status', 'draft')->update(['status' => 'active']);
        $vendor->user()->update(['role' => 'vendor']);

        return back()->with('success', 'Vendor approved successfully.');
    }
}
