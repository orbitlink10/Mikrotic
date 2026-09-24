<?php

namespace Tests\Feature;

use App\Models\HomepageContent;
use App\Models\Testimonial;
use App\Models\User;
use DOMDocument;
use DOMXPath;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class HomepageContentAccuracyTest extends TestCase
{
    use RefreshDatabase;

    public function test_default_homepage_has_equipment_faqs_matching_schema_and_no_sample_reviews(): void
    {
        $response = $this->get('/')->assertOk();

        foreach (['router, switch or access point', 'include Wi-Fi', 'included with a MikroTik product', 'configuration or installation', 'make payment', 'collect my order', 'warranty applies'] as $topic) {
            $response->assertSee($topic);
        }

        foreach (['satellite internet', 'dish alignment', 'kit includes the dish', 'speeds vary by location, weather', 'testimonial-card', 'Managed from admin'] as $unwanted) {
            $response->assertDontSee($unwanted);
        }

        $this->assertDatabaseCount('testimonials', 0);
        $this->assertCount(7, $this->assertFaqMatchesStructuredData($response));
    }

    public function test_migration_replaces_saved_satellite_copy_and_hides_bundled_reviews(): void
    {
        $homepage = HomepageContent::create([
            'site_key' => 'default',
            'hero_title' => 'MikroTik Kenya',
            'hero_description' => 'Networking equipment for your next project.',
            'content_body' => '<p>Keep this networking guide.</p>',
            'faq_title' => 'Frequently Asked Questions',
            'faq_items' => [
                ['question' => 'What speeds should customers expect?', 'answer' => 'Actual speeds vary by location, weather, and network demand.'],
                ['question' => 'Do you offer installation support in Kenya?', 'answer' => 'Installation includes dish alignment.'],
                ['question' => 'What is included in a Mikrotik Kenya kit?', 'answer' => 'A typical kit includes the dish, router, power supply, mounting hardware, and cables.'],
            ],
            'why_choose_intro' => 'Get dependable high-speed internet for homes and businesses.',
            'why_choose_items' => [['title' => 'Flexible Payments', 'description' => 'Practical options for hardware, setup, and monthly service.']],
            'testimonials_intro' => 'Reliable satellite internet is changing how families stay connected.',
        ]);

        $quotes = [
            'The installation team arrived on time, explained the ideal mounting position, and got us online the same day. The experience felt professional from start to finish.',
            'Our children now attend online classes without interruptions, and video meetings are finally stable. Starlink has made a visible difference in our day-to-day routine.',
            'Our children now attend online classes without interruptions, and video meetings are finally stable. Mikrotik Kenya has made a visible difference in our day-to-day routine.',
            'Uploads that used to take forever now finish quickly, which matters a lot for my content work. For creators working outside strong fiber zones, this is a serious upgrade.',
        ];
        foreach ($quotes as $quote) {
            Testimonial::create(['name' => 'Sample customer', 'role' => 'Customer', 'quote' => $quote, 'is_active' => true]);
        }

        $migration = require database_path('migrations/2026_09_24_000001_refresh_homepage_store_content.php');
        $migration->up();
        $firstRun = $homepage->fresh()->getAttributes();
        $migration->up();

        $this->assertSame($firstRun, $homepage->fresh()->getAttributes());
        $this->assertSame('<p>Keep this networking guide.</p>', $homepage->fresh()->content_body);
        $this->assertSame('MikroTik Kenya', $homepage->fresh()->hero_title);
        $this->assertSame(4, Testimonial::where('is_active', false)->count());

        $response = $this->get('/')->assertOk();
        $response->assertSee('Check Compatibility');
        $response->assertDontSee('satellite internet');
        $response->assertDontSee('monthly service');
        $response->assertDontSee('dish alignment');
        $response->assertDontSee('kit includes the dish');
        $response->assertDontSee('weather');
        $response->assertDontSee('Sample customer');
        $response->assertDontSee('home-section--testimonials');
        $this->assertCount(7, $this->assertFaqMatchesStructuredData($response));
    }

    public function test_admin_faq_edits_update_visible_answers_and_schema_and_survive_migration(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $faq = [['question' => 'Can I request a router & switch quotation?', 'answer' => 'Send the model numbers & quantities to our sales team.']];
        $this->actingAs($admin)->post('/admin/pages-content', [
            'section' => 'faq',
            'faq_title' => 'Ordering equipment',
            'faq_items' => $faq,
        ])->assertSessionHasNoErrors()->assertRedirect('/admin/pages-content');

        $testimonial = Testimonial::create([
            'name' => 'Equipment customer',
            'role' => 'Buyer',
            'quote' => 'The switch delivered matched our equipment order.',
            'is_active' => true,
        ]);
        $migration = require database_path('migrations/2026_09_24_000001_refresh_homepage_store_content.php');
        $migration->up();

        $response = $this->get('/')->assertOk();
        $this->assertSame($faq, $this->assertFaqMatchesStructuredData($response));
        $response->assertSee('Ordering equipment');
        $response->assertSee($testimonial->quote);
        $this->assertTrue($testimonial->fresh()->is_active);
    }

    public function test_missing_testimonial_storage_does_not_display_fabricated_fallbacks(): void
    {
        Schema::drop('testimonials');

        $this->get('/')->assertOk()
            ->assertDontSee('home-section--testimonials')
            ->assertDontSee('Joan K.')
            ->assertDontSee('Samuel O.')
            ->assertDontSee('Victor M.');
    }

    private function assertFaqMatchesStructuredData(TestResponse $response): array
    {
        $document = new DOMDocument;
        $previous = libxml_use_internal_errors(true);
        try {
            $document->loadHTML($response->getContent());
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }
        $xpath = new DOMXPath($document);
        $visibleItems = [];
        foreach ($xpath->query('//details[@class="faq-item"]') as $item) {
            $visibleItems[] = [
                'question' => trim($xpath->evaluate('string(summary)', $item)),
                'answer' => trim($xpath->evaluate('string(p)', $item)),
            ];
        }

        $faqSchemas = [];
        foreach ($xpath->query('//script[@type="application/ld+json"]') as $script) {
            $schema = json_decode($script->textContent, true, 512, JSON_THROW_ON_ERROR);
            if (($schema['@type'] ?? null) === 'FAQPage') {
                $faqSchemas[] = $schema;
            }
        }
        $this->assertCount(1, $faqSchemas);
        $schemaItems = array_map(fn (array $item): array => [
            'question' => $item['name'],
            'answer' => $item['acceptedAnswer']['text'],
        ], $faqSchemas[0]['mainEntity']);
        $this->assertNotEmpty($visibleItems);
        $this->assertSame($visibleItems, $schemaItems);

        return $visibleItems;
    }
}
