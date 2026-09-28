<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Models\Vendor;
use App\Support\SeoMetadata;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class MikrotikProductTitleTest extends TestCase
{
    use RefreshDatabase;

    #[DataProvider('productTitles')]
    public function test_rendered_titles_use_the_exact_model_and_correct_type(string $model, string $type): void
    {
        // Deliberately misleading category and old override: neither should label
        // wired models as LTE routers or cause repeated branding in the title.
        $product = $this->createProduct([
            'name' => 'MikroTik '.$model,
            'model_number' => $model,
            'seo_title' => $model.' Price in Kenya | MikroTik MikroTik LTE Router',
        ]);
        $before = $product->fresh()->getRawOriginal();

        $response = $this->get('/product/'.$product->slug)->assertOk();
        preg_match_all('/<title>(.*?)<\/title>/s', $response->getContent(), $matches);

        $this->assertCount(1, $matches[1]);
        $title = html_entity_decode($matches[1][0], ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $this->assertSame('MikroTik '.$model.' Price in Kenya | '.$type, $title);
        $this->assertSame(1, substr_count($title, 'MikroTik'));
        $this->assertSame($before, $product->fresh()->getRawOriginal());
        $response->assertSee($product->name);
        $response->assertSee('Original description stays unchanged.');
    }

    public static function productTitles(): array
    {
        return [
            'wired RB4011' => ['RB4011iGS+RM', 'Gigabit Router'],
            'RB5009 punctuation' => ['RB5009UPr+S+IN', 'Gigabit Router'],
            'hEX model' => ['RB750Gr3', 'Gigabit Router'],
            'hEX S model' => ['RB760iGS', 'Gigabit Router'],
            'wired L009' => ['L009UiGS-RM', 'Gigabit Router'],
            '5G substring in wired code' => ['RB951G-2HnD', 'Router'],
            'CCR punctuation' => ['CCR2004-1G-12S+2XS', 'Cloud Core Router'],
            'CRS with SFP ports' => ['CRS326-24G-2S+RM', 'Switch'],
            'CSS with 5G substring' => ['CSS106-5G-1S', 'Switch'],
            'wireless RB4011 variant' => ['RB4011iGS+5HacQ2HnD-IN', 'Router'],
            'access point' => ['cAP ax', 'Access Point'],
            'wireless router' => ['hAP ax³', 'Wireless Router'],
            'wireless radio' => ['LHG 5 ac', 'Wireless System'],
            'cellular router' => ['Chateau LTE12', 'LTE Router'],
            '5G cellular router' => ['Chateau 5G ax', '5G Router'],
            'LTE modem' => ['R11e-LTE6', 'LTE Modem'],
            'LTE antenna' => ['mANT LTE 5o', 'Antenna'],
            'outdoor access point' => ['mANTBox 19s', 'Outdoor Access Point'],
            'SFP plus module' => ['S+RJ10', 'Transceiver Module'],
            'direct attach cable' => ['S+DA0001', 'Network Cable'],
            'PoE accessory' => ['RBPOE', 'PoE Injector'],
            'software' => ['RouterOS Level 4', 'Software'],
        ];
    }

    public function test_new_product_default_removes_brand_repetitions_without_changing_the_model(): void
    {
        $product = $this->createProduct([
            'name' => 'Mikrotik MikroTik RB4011iGS+RM',
            'model_number' => null,
            'seo_title' => null,
        ]);

        $this->get('/product/'.$product->slug)
            ->assertOk()
            ->assertSee('<title>MikroTik RB4011iGS+RM Price in Kenya | Gigabit Router</title>', false);

        $product->model_number = 'MikroTik MikroTik RB4011iGS+RM';
        $this->assertSame('MikroTik RB4011iGS+RM Price in Kenya | Gigabit Router', SeoMetadata::productTitle($product));
    }

    public function test_long_models_are_not_truncated_and_html_characters_are_escaped(): void
    {
        $model = 'Model + / . (Rev. 2) & '.str_repeat('Long Model ', 8).'End';
        $product = $this->createProduct(['model_number' => $model]);

        $this->get('/product/'.$product->slug)
            ->assertOk()
            ->assertSee('<title>'.e('MikroTik '.$model.' Price in Kenya | Networking Equipment').'</title>', false);
    }

    public function test_brand_and_parent_category_identify_mikrotik_products(): void
    {
        $product = $this->createProduct(['name' => 'RB4011iGS+RM', 'brand' => 'mIkRoTiK']);
        $product->setRelation('category', null);
        $expected = 'MikroTik RB4011iGS+RM Price in Kenya | Gigabit Router';
        $this->assertSame($expected, SeoMetadata::productTitle($product));

        $product->brand = null;
        $this->assertSame($expected, SeoMetadata::productTitle($product));

        $parent = Category::create(['name' => 'MikroTik', 'slug' => 'mikrotik']);
        $child = Category::create(['name' => 'Indoor', 'slug' => 'indoor', 'parent_id' => $parent->id]);
        $product->name = 'cAP ax';
        $product->setRelation('category', $child);
        $this->assertSame('MikroTik cAP ax Price in Kenya | Access Point', SeoMetadata::productTitle($product));
    }

    public function test_other_brands_keep_their_existing_title_behavior(): void
    {
        $product = $this->createProduct([
            'name' => 'Other Brand Router',
            'brand' => 'Other Brand',
            'model_number' => 'Model A',
            'seo_title' => 'Original custom SEO title',
        ]);

        $this->get('/product/'.$product->slug)
            ->assertOk()
            ->assertSee('<title>Original custom SEO title</title>', false);

        $product->seo_title = null;
        $this->assertSame('Model A Price in Kenya | MikroTik LTE Router', SeoMetadata::productTitle($product));
    }

    private function createProduct(array $attributes = []): Product
    {
        $category = Category::create(['name' => 'MikroTik LTE & 5G Routers', 'slug' => 'mikrotik-lte-5g']);
        $vendor = Vendor::create([
            'user_id' => User::factory()->create()->id,
            'shop_name' => 'Test Store',
            'slug' => 'test-store',
            'phone' => '0712345678',
            'address' => 'Nairobi',
            'is_approved' => true,
        ]);

        return Product::create(array_merge([
            'vendor_id' => $vendor->id,
            'category_id' => $category->id,
            'name' => 'MikroTik Test Model',
            'slug' => 'original-product-url',
            'sku' => 'INTERNAL-SKU',
            'description' => '<p>Original description stays unchanged.</p>',
            'price' => '29000.00',
            'stock' => 3,
            'status' => 'active',
        ], $attributes));
    }
}
