<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\HomepageContent;
use App\Models\Order;
use App\Models\Page;
use App\Models\Product;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
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

    public function test_admin_product_create_page_displays_form(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'status' => 'active',
            'phone' => '0700000000',
        ]);

        $response = $this->actingAs($admin)->get('/admin/products/create');

        $response->assertOk();
        $response->assertSee('Post Product');
        $response->assertSee('Product Name');
        $response->assertSee('Description');
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
        $response->assertSee('Add New Post');
        $response->assertSee('Meta Title');
        $response->assertSee('Meta Description');
        $response->assertSee('Page Title');
        $response->assertSee('Image Alt Text');
        $response->assertSee('Heading 2');
        $response->assertSee('Page Description:');
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

        $response = $this->actingAs($admin)->post('/admin/products', [
            'name' => 'Admin Camera',
            'category_id' => $category->id,
            'description' => '<p>Camera added by <strong>admin</strong>.</p><script>alert(1)</script>',
            'meta_description' => 'Compact admin camera listing.',
            'price' => '499.99',
            'stock' => 8,
            'image_url' => 'https://example.com/camera.jpg',
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

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Admin Camera',
            'category_id' => $category->id,
            'meta_description' => 'Compact admin camera listing.',
        ]);

        $this->assertDatabaseHas('product_images', [
            'product_id' => $product->id,
            'image_url' => 'https://example.com/camera.jpg',
        ]);
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
}
