<?php

namespace Tests\Feature;

use App\Models\HomepageContent;
use App\Models\Page;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StorefrontNavigationTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_navigation_falls_back_to_latest_pages_first(): void
    {
        $olderPage = $this->createPage('Older Navigation Article', 'older-navigation-article');
        $olderPage->forceFill([
            'created_at' => now()->subDays(2),
            'updated_at' => now()->subDays(2),
        ])->save();

        $newerPage = $this->createPage('Newest Navigation Article', 'newest-navigation-article');
        $newerPage->forceFill([
            'created_at' => now()->subDay(),
            'updated_at' => now()->subDay(),
        ])->save();

        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('class="primary-nav"', false);
        $response->assertSeeInOrder([
            'Newest Navigation Article',
            'Older Navigation Article',
        ]);
    }

    public function test_homepage_content_menu_overrides_latest_page_fallback(): void
    {
        $this->createPage('Fallback Navigation Article', 'fallback-navigation-article');

        HomepageContent::create([
            'site_key' => HomepageContent::DEFAULT_SITE_KEY,
            'hero_title' => 'MikroTik Kenya',
            'hero_description' => 'Networking equipment for Kenya.',
            'nav_menu_items' => [
                ['label' => 'Support Desk', 'url' => '/support'],
                ['label' => 'Router Deals', 'url' => '/category/mikrotik-router-prices-in-kenya'],
            ],
        ]);

        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('Support Desk');
        $response->assertSee('/category/mikrotik-router-prices-in-kenya', false);
        $response->assertDontSee('Fallback Navigation Article');
    }

    public function test_admin_homepage_content_save_controls_nav_menu_items(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'status' => 'active',
        ]);

        $this->actingAs($admin)
            ->post(route('admin.pages-content.update'), [
                'hero_title' => 'MikroTik Kenya',
                'hero_description' => 'Networking equipment for Kenya.',
                'nav_menu_items' => [
                    ['label' => 'Routers', 'url' => 'category/mikrotik-router-prices-in-kenya'],
                    ['label' => 'Contact', 'url' => '/contact-us'],
                    ['label' => '', 'url' => '/blank-label'],
                    ['label' => 'Unsafe', 'url' => 'javascript:alert(1)'],
                ],
            ])
            ->assertRedirect(route('admin.pages-content.edit'));

        $homepageContent = HomepageContent::query()
            ->where('site_key', HomepageContent::DEFAULT_SITE_KEY)
            ->firstOrFail();

        $this->assertSame([
            ['label' => 'Routers', 'url' => '/category/mikrotik-router-prices-in-kenya'],
            ['label' => 'Contact', 'url' => '/contact-us'],
        ], $homepageContent->navMenuItems());
    }

    private function createPage(string $title, string $slug): Page
    {
        return Page::create([
            'meta_title' => $title,
            'meta_description' => 'Navigation test page content.',
            'title' => $title,
            'heading_two' => $title,
            'slug' => $slug,
            'type' => 'post',
            'body' => '<p>Navigation page body.</p>',
        ]);
    }
}
