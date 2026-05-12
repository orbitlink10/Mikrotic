<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\HomepageContent;
use App\Models\Order;
use App\Models\Page;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Tests\TestCase;

class AdminProductManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_dashboard_displays_sidebar_dashboard_structure(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'status' => 'active',
            'phone' => '0700000000',
        ]);

        $response = $this->actingAs($admin)->get('/admin');

        $response->assertOk();
        $response->assertSee('Admin Overview');
        $response->assertSee('Dashboard');
        $response->assertSee('Content Management');
        $response->assertSee('Recent Orders');
        $response->assertDontSee('Post Product');
    }

    public function test_admin_can_view_management_index_pages(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'status' => 'active',
            'phone' => '0700000000',
        ]);

        Category::create([
            'name' => 'Networking',
            'slug' => 'networking',
        ]);

        Page::create([
            'title' => 'Landing Page',
            'slug' => 'landing-page',
            'type' => 'page',
        ]);

        Order::create([
            'user_id' => $admin->id,
            'order_number' => 'ORD-1001',
            'status' => 'pending',
            'total_amount' => 1000,
            'shipping_name' => 'Admin User',
            'shipping_email' => 'admin@example.com',
            'shipping_phone' => '0700000000',
            'shipping_address' => 'Nairobi',
            'payment_method' => 'cash_on_delivery',
        ]);

        $this->actingAs($admin)->get('/admin/categories')->assertOk()->assertSee('Categories');
        $this->actingAs($admin)->get('/admin/products')->assertOk()->assertSee('Products');
        $this->actingAs($admin)->get('/admin/orders')->assertOk()->assertSee('Orders');
        $this->actingAs($admin)->get('/admin/pages')->assertOk()->assertSee('Pages');
        $this->actingAs($admin)->get('/admin/pages-content')->assertOk()->assertSee('Update Homepage Content');
    }

    public function test_admin_category_create_page_displays_requested_fields(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'status' => 'active',
            'phone' => '0700000000',
        ]);

        $response = $this->actingAs($admin)->get('/admin/categories/create');

        $response->assertOk();
        $response->assertSee('Create Category');
        $response->assertSee('Meta description');
        $response->assertSee('Description (Optional)');
        $response->assertSee('Parent Category and Image (Optional)');
        $response->assertSee('Upload Image');
    }

    public function test_admin_can_create_category_with_meta_and_description(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'status' => 'active',
            'phone' => '0700000000',
        ]);

        $longDescription = '<p>' . str_repeat('A', 6005) . '</p>';
        $image = UploadedFile::fake()->create('category.jpg', 64, 'image/jpeg');

        $response = $this->actingAs($admin)->post('/admin/categories', [
            'name' => 'Networking Guides',
            'meta_description' => 'Helpful networking category summaries for search and navigation.',
            'description' => $longDescription,
            'image' => $image,
        ]);

        $response->assertRedirect('/admin/categories');
        $response->assertSessionHas('success');

        $category = Category::query()->where('slug', 'networking-guides')->first();

        $this->assertNotNull($category);
        $this->assertSame($longDescription, $category->description);
        $this->assertSame('Helpful networking category summaries for search and navigation.', $category->meta_description);
        $this->assertNotNull($category->image_url);
        $this->assertStringStartsWith('/uploads/categories/', $category->image_url);

        $uploadedPath = public_path(ltrim((string) $category->image_url, '/\\'));
        $this->assertFileExists($uploadedPath);

        File::delete($uploadedPath);
    }

    public function test_admin_can_create_category_when_content_columns_are_missing(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'status' => 'active',
            'phone' => '0700000000',
        ]);

        Schema::table('categories', function (Blueprint $table): void {
            $table->dropColumn(['meta_description', 'description']);
        });

        $response = $this->actingAs($admin)->post('/admin/categories', [
            'name' => 'Switches',
            'meta_description' => 'This should not crash when columns are missing.',
            'description' => '<p>Still create the category.</p>',
        ]);

        $response->assertRedirect('/admin/categories');
        $response->assertSessionHas('success', 'Category saved. Run php artisan migrate to enable category meta description and description storage.');

        $this->assertDatabaseHas('categories', [
            'name' => 'Switches',
            'slug' => 'switches',
        ]);
    }

    public function test_admin_product_create_page_displays_form(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'status' => 'active',
            'phone' => '0700000000',
        ]);

        $response = $this->actingAs($admin)->get('/admin/products/create');

        $response->assertOk();
        $response->assertSee('Add Product');
        $response->assertSee('Product Name');
        $response->assertSee('Marked Price (KES)');
        $response->assertSee('Subcategory');
        $response->assertSee('Description');
    }

    public function test_admin_products_index_renders_working_update_and_delete_actions(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'status' => 'active',
            'phone' => '0700000000',
        ]);

        $category = Category::create([
            'name' => 'Routers',
            'slug' => 'routers',
        ]);

        $vendor = Vendor::create([
            'user_id' => $admin->id,
            'shop_name' => 'Admin Store',
            'slug' => 'admin-store',
            'description' => 'Products managed by admin.',
            'phone' => '0700000000',
            'address' => 'Nairobi',
            'is_approved' => true,
        ]);

        $product = Product::create([
            'vendor_id' => $vendor->id,
            'category_id' => $category->id,
            'name' => 'RB5009',
            'slug' => 'rb5009',
            'description' => '<p>Managed switch router.</p>',
            'meta_description' => 'RB5009 router listing.',
            'price' => '56000.00',
            'stock' => 3,
            'sku' => 'SKU-RB5009',
            'status' => 'active',
        ]);

        $response = $this->actingAs($admin)->get('/admin/products');

        $response->assertOk();
        $response->assertSee(route('admin.products.edit', $product), false);
        $response->assertSee(route('admin.products.destroy', $product), false);
    }

    public function test_admin_page_create_page_displays_requested_post_fields(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'status' => 'active',
            'phone' => '0700000000',
        ]);

        $response = $this->actingAs($admin)->get('/admin/pages/create');

        $response->assertOk();
        $response->assertSee('Manage Pages');
        $response->assertSee('Fill in the page details below to publish new content');
        $response->assertSee('Meta Title');
        $response->assertSee('Meta Description');
        $response->assertSee('Page Title');
        $response->assertSee('Heading 2');
        $response->assertSee('Page Description');
        $response->assertSee('Optional Slug and Image');
    }

    public function test_admin_can_post_products_from_admin_create_page(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'status' => 'active',
            'phone' => '0700000000',
        ]);

        $category = Category::create([
            'name' => 'Electronics',
            'slug' => 'electronics',
        ]);

        $image = UploadedFile::fake()->create('camera.jpg', 64, 'image/jpeg');

        $response = $this->actingAs($admin)->post('/admin/products', [
            'name' => 'Admin Camera',
            'category_id' => $category->id,
            'description' => '<p>Camera added by <strong>admin</strong>.</p><script>alert(1)</script>',
            'meta_description' => 'Compact admin camera listing.',
            'price' => '499.99',
            'compare_at_price' => '579.99',
            'stock' => 8,
            'image' => $image,
        ]);

        $response->assertRedirect('/admin/products');
        $response->assertSessionHas('success');

        $vendor = Vendor::query()->where('user_id', $admin->id)->first();

        $this->assertNotNull($vendor);
        $this->assertTrue((bool) $vendor->is_approved);

        $product = Product::query()->first();

        $this->assertNotNull($product);
        $this->assertSame($vendor->id, $product->vendor_id);
        $this->assertSame('active', $product->status);
        $this->assertSame('Compact admin camera listing.', $product->meta_description);
        $this->assertSame('<p>Camera added by <strong>admin</strong>.</p>', $product->description);
        $this->assertSame('579.99', $product->compare_at_price);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Admin Camera',
            'category_id' => $category->id,
            'meta_description' => 'Compact admin camera listing.',
            'compare_at_price' => '579.99',
        ]);

        $productImage = ProductImage::query()->where('product_id', $product->id)->first();

        $this->assertNotNull($productImage);
        $this->assertStringStartsWith('/uploads/products/', (string) $productImage->image_url);

        $uploadedPath = public_path(ltrim((string) $productImage->image_url, '/\\'));
        $this->assertFileExists($uploadedPath);

        File::delete($uploadedPath);
    }

    public function test_admin_can_create_a_new_category_while_posting_a_product(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'status' => 'active',
            'phone' => '0700000000',
        ]);

        $response = $this->actingAs($admin)->post('/admin/products', [
            'name' => 'Router Board',
            'category_name' => 'Networking',
            'description' => '<p>Managed by admin.</p>',
            'meta_description' => 'Networking product from the admin dashboard.',
            'price' => '12999.00',
            'stock' => 4,
        ]);

        $response->assertRedirect('/admin/products');
        $response->assertSessionHas('success');

        $category = Category::query()->where('name', 'Networking')->first();
        $product = Product::query()->where('name', 'Router Board')->first();

        $this->assertNotNull($category);
        $this->assertNotNull($product);
        $this->assertSame($category->id, $product->category_id);

        $this->assertDatabaseHas('categories', [
            'name' => 'Networking',
            'slug' => 'networking',
        ]);
    }

    public function test_admin_can_assign_product_to_selected_subcategory(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'status' => 'active',
            'phone' => '0700000000',
        ]);

        $category = Category::create([
            'name' => 'Networking',
            'slug' => 'networking',
        ]);

        $subcategory = Category::create([
            'name' => 'Routers',
            'slug' => 'routers',
            'parent_id' => $category->id,
        ]);

        $response = $this->actingAs($admin)->post('/admin/products', [
            'name' => 'Core Router',
            'category_id' => $category->id,
            'subcategory_id' => $subcategory->id,
            'description' => '<p>Enterprise router.</p>',
            'meta_description' => 'Enterprise-grade router for branch and core networks.',
            'price' => '74999.00',
            'compare_at_price' => '81999.00',
            'stock' => 2,
        ]);

        $response->assertRedirect('/admin/products');
        $response->assertSessionHas('success');

        $product = Product::query()->where('name', 'Core Router')->first();

        $this->assertNotNull($product);
        $this->assertSame($subcategory->id, $product->category_id);
        $this->assertSame('81999.00', $product->compare_at_price);
    }

    public function test_admin_can_delete_product_from_admin_products_index(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'status' => 'active',
            'phone' => '0700000000',
        ]);

        $category = Category::create([
            'name' => 'Switches',
            'slug' => 'switches',
        ]);

        $vendor = Vendor::create([
            'user_id' => $admin->id,
            'shop_name' => 'Admin Store',
            'slug' => 'admin-store',
            'description' => 'Products managed by admin.',
            'phone' => '0700000000',
            'address' => 'Nairobi',
            'is_approved' => true,
        ]);

        $product = Product::create([
            'vendor_id' => $vendor->id,
            'category_id' => $category->id,
            'name' => 'CRS326',
            'slug' => 'crs326',
            'description' => '<p>Core switch.</p>',
            'meta_description' => 'CRS326 switch listing.',
            'price' => '31000.00',
            'stock' => 5,
            'sku' => 'SKU-CRS326',
            'status' => 'active',
        ]);

        ProductImage::create([
            'product_id' => $product->id,
            'image_url' => '/uploads/products/crs326.jpg',
            'is_primary' => true,
            'sort_order' => 0,
        ]);

        $response = $this->actingAs($admin)->delete(route('admin.products.destroy', $product));

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Product deleted successfully.');
        $this->assertDatabaseMissing('products', ['id' => $product->id]);
        $this->assertDatabaseMissing('product_images', ['product_id' => $product->id]);
    }

    public function test_admin_can_create_content_page(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'status' => 'active',
            'phone' => '0700000000',
        ]);

        $response = $this->actingAs($admin)->post('/admin/pages', [
            'meta_title' => 'Support Page Meta Title',
            'meta_description' => 'Support page meta description for search and content previews.',
            'title' => 'Support Page',
            'heading_two' => 'Support Options',
            'type' => 'page',
            'alt_text' => 'Support page hero image',
            'body' => '<p>Support content</p><script>alert(1)</script><pre><code>safe code</code></pre>',
        ]);

        $response->assertRedirect('/admin/pages');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('pages', [
            'title' => 'Support Page',
            'slug' => 'support-page',
            'type' => 'page',
            'meta_title' => 'Support Page Meta Title',
            'meta_description' => 'Support page meta description for search and content previews.',
            'heading_two' => 'Support Options',
            'alt_text' => 'Support page hero image',
        ]);

        $page = Page::query()->where('slug', 'support-page')->first();

        $this->assertNotNull($page);
        $this->assertSame('<p>Support content</p><pre><code>safe code</code></pre>', $page->body);
    }

    public function test_admin_page_preview_links_to_public_page_view(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'status' => 'active',
            'phone' => '0700000000',
        ]);

        $page = Page::create([
            'meta_title' => 'Preview Meta Title',
            'meta_description' => 'Preview page meta description.',
            'title' => 'Preview Page',
            'heading_two' => 'Preview Section',
            'slug' => 'preview-page',
            'type' => 'post',
            'alt_text' => 'Preview page image',
            'body' => '<p>Preview body content.</p>',
        ]);

        $this->actingAs($admin)
            ->get('/admin/pages')
            ->assertOk()
            ->assertSee(route('pages.show', ['page' => $page->slug]), false);

        $this->get('/pages/' . $page->slug)
            ->assertOk()
            ->assertSee('Preview Page')
            ->assertSee('Preview Section')
            ->assertSee('Preview body content.');
    }

    public function test_admin_pages_index_shows_clear_error_when_table_is_missing(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'status' => 'active',
            'phone' => '0700000000',
        ]);

        Schema::drop('pages');

        $response = $this->actingAs($admin)->get('/admin/pages');

        $response->assertOk();
        $response->assertSee('Page storage is not ready yet.');
        $response->assertSee('php artisan migrate');
    }

    public function test_admin_page_create_shows_clear_error_when_table_is_missing(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'status' => 'active',
            'phone' => '0700000000',
        ]);

        Schema::drop('pages');

        $response = $this->actingAs($admin)->get('/admin/pages/create');

        $response->assertOk();
        $response->assertSee('Page storage is not ready yet.');
        $response->assertSee('php artisan migrate');
    }

    public function test_admin_page_create_submit_shows_clear_error_when_table_is_missing(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'status' => 'active',
            'phone' => '0700000000',
        ]);

        Schema::drop('pages');

        $response = $this->actingAs($admin)->post('/admin/pages', [
            'meta_title' => 'Support Page Meta Title',
            'meta_description' => 'Support page meta description for search and content previews.',
            'title' => 'Support Page',
            'heading_two' => 'Support Options',
            'type' => 'page',
            'alt_text' => 'Support page hero image',
            'body' => '<p>Support content</p>',
        ]);

        $response->assertRedirect('/admin/pages');
        $response->assertSessionHas('error', 'Page storage is not ready yet. Run php artisan migrate to create the pages table.');
    }

    public function test_admin_can_update_homepage_content(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'status' => 'active',
            'phone' => '0700000000',
        ]);

        $response = $this->actingAs($admin)->post('/admin/pages-content', [
            'hero_title' => 'Starlink Kenya for Homes and Business',
            'hero_description' => 'Deploy reliable satellite internet across homes, offices, remote sites, and branch networks from one storefront.',
        ]);

        $response->assertRedirect('/admin/pages-content');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('homepage_contents', [
            'site_key' => HomepageContent::DEFAULT_SITE_KEY,
            'hero_title' => 'Starlink Kenya for Homes and Business',
        ]);

        $homeResponse = $this->get('/');
        $homeResponse->assertOk();
        $homeResponse->assertSee('Starlink Kenya for Homes and Business');
        $homeResponse->assertSee('Deploy reliable satellite internet across homes, offices, remote sites, and branch networks from one storefront.');
    }

    public function test_storefront_uses_default_homepage_content_when_table_is_missing(): void
    {
        Schema::drop('homepage_contents');

        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('Starlink Kenya | High-Speed Satellite Internet Across Kenya');
        $response->assertSee('Starlink Kenya offers high-speed satellite internet with affordable packages, hardware, and monthly plans. Stay connected anywhere in Kenya today.');
    }

    public function test_admin_homepage_update_shows_clear_error_when_table_is_missing(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'status' => 'active',
            'phone' => '0700000000',
        ]);

        Schema::drop('homepage_contents');

        $response = $this->actingAs($admin)->post('/admin/pages-content', [
            'hero_title' => 'Fallback title',
            'hero_description' => 'Fallback description that should not be saved because the table is missing.',
        ]);

        $response->assertRedirect('/admin/pages-content');
        $response->assertSessionHas('error', 'Homepage content storage is not ready yet. Run php artisan migrate to create the homepage_contents table.');
    }
}
