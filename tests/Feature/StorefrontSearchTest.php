<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StorefrontSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_exact_search_redirects_to_the_matching_product_page(): void
    {
        [$product] = $this->createCatalogProducts();

        $response = $this->get('/?search=' . urlencode('RB5009UPr+S+IN'));

        $response->assertRedirect(route('product.show', $product));
    }

    public function test_partial_search_shows_filtered_catalog_results(): void
    {
        [$matchingProduct, $otherProduct] = $this->createCatalogProducts();

        $response = $this->get('/?search=5009');

        $response->assertOk();
        $response->assertSee('Results for "5009"', false);
        $response->assertSee($matchingProduct->name);
        $response->assertDontSee($otherProduct->name);
    }

    public function test_category_page_displays_saved_category_content(): void
    {
        $category = Category::create([
            'name' => 'Mikrotik Wired Routers Price in Kenya',
            'slug' => 'mikrotik-wired-routers-price-in-kenya',
            'meta_description' => 'Wired router price guide.',
            'description' => '<h2>RouterBOARD options</h2><p>Compare MikroTik wired routers for homes, businesses, and ISPs.</p><script>alert(1)</script>',
        ]);

        $response = $this->get(route('category.show', $category));

        $response->assertOk();
        $response->assertSee('Mikrotik Wired Routers Price in Kenya');
        $response->assertSee('RouterBOARD options');
        $response->assertSee('Compare MikroTik wired routers for homes, businesses, and ISPs.');
        $response->assertDontSee('<script', false);
    }

    public function test_catalog_uses_local_placeholder_when_product_has_no_image(): void
    {
        [$product] = $this->createCatalogProducts();
        $product->images()->delete();

        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('/assets/product-placeholder.svg', false);
        $response->assertDontSee('via.placeholder.com', false);
    }

    /**
     * @return array{0: \App\Models\Product, 1: \App\Models\Product}
     */
    private function createCatalogProducts(): array
    {
        $vendorUser = User::factory()->create([
            'role' => 'vendor',
            'status' => 'active',
            'phone' => '0712345678',
        ]);

        $vendor = Vendor::create([
            'user_id' => $vendorUser->id,
            'shop_name' => 'Mikrotik Kenya Store',
            'slug' => 'mikrotik-kenya-store',
            'description' => 'Network and routing equipment.',
            'phone' => '0712345678',
            'address' => 'Nairobi',
            'is_approved' => true,
        ]);

        $category = Category::create([
            'name' => 'Mikrotik Ethernet Routers',
            'slug' => 'mikrotik-ethernet-routers',
        ]);

        $matchingProduct = Product::create([
            'vendor_id' => $vendor->id,
            'category_id' => $category->id,
            'name' => 'RB5009UPr+S+IN',
            'slug' => 'rb5009uprsin',
            'description' => '<p>Enterprise router with PoE support.</p>',
            'meta_description' => 'High-performance MikroTik router for ISP and business use.',
            'price' => '56000.00',
            'compare_at_price' => '57000.00',
            'stock' => 3,
            'sku' => 'RB5009UPr+S+IN',
            'status' => 'active',
        ]);

        $otherProduct = Product::create([
            'vendor_id' => $vendor->id,
            'category_id' => $category->id,
            'name' => 'CRS326-24G-2S+RM',
            'slug' => 'crs326-24g-2s-rm',
            'description' => '<p>Rackmount switch.</p>',
            'meta_description' => 'Managed gigabit switch.',
            'price' => '31000.00',
            'stock' => 4,
            'sku' => 'CRS326-24G-2S+RM',
            'status' => 'active',
        ]);

        ProductImage::create([
            'product_id' => $matchingProduct->id,
            'image_url' => 'https://example.com/rb5009.jpg',
            'is_primary' => true,
            'sort_order' => 0,
        ]);

        ProductImage::create([
            'product_id' => $otherProduct->id,
            'image_url' => 'https://example.com/crs326.jpg',
            'is_primary' => true,
            'sort_order' => 0,
        ]);

        return [$matchingProduct, $otherProduct];
    }
}
