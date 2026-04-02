<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminProductManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_post_products_from_dashboard(): void
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

        $response->assertRedirect('/admin');
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
}
