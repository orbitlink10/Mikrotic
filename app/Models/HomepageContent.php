<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class HomepageContent extends Model
{
    use HasFactory;

    public const DEFAULT_SITE_KEY = 'default';

    protected $fillable = [
        'site_key',
        'site_logo_path',
        'contact_phone',
        'contact_whatsapp',
        'hero_title',
        'hero_description',
        'hero_image_path',
        'why_choose_title',
        'why_choose_intro',
        'why_choose_items',
        'testimonials_badge',
        'testimonials_title',
        'testimonials_intro',
        'testimonial_items',
        'faq_badge',
        'faq_title',
        'faq_intro',
        'faq_items',
        'content_badge',
        'content_title',
        'content_intro',
        'content_body',
        'featured_product_ids',
    ];

    protected $casts = [
        'why_choose_items' => 'array',
        'testimonial_items' => 'array',
        'faq_items' => 'array',
        'featured_product_ids' => 'array',
    ];

    public static function current(): self
    {
        if (! static::storageReady()) {
            return static::defaultContent();
        }

        return static::query()->where('site_key', static::DEFAULT_SITE_KEY)->first()
            ?? static::defaultContent();
    }

    public static function storageReady(): bool
    {
        $table = (new static)->getTable();

        if (! Schema::hasTable($table)) {
            return false;
        }

        foreach ([
            'site_key',
            'site_logo_path',
            'contact_phone',
            'contact_whatsapp',
            'hero_title',
            'hero_description',
            'hero_image_path',
            'why_choose_title',
            'why_choose_intro',
            'why_choose_items',
            'testimonials_badge',
            'testimonials_title',
            'testimonials_intro',
            'testimonial_items',
            'faq_badge',
            'faq_title',
            'faq_intro',
            'faq_items',
            'content_badge',
            'content_title',
            'content_intro',
            'content_body',
            'featured_product_ids',
        ] as $column) {
            if (! Schema::hasColumn($table, $column)) {
                return false;
            }
        }

        return true;
    }

    private static function defaultContent(): self
    {
        return new static([
            'site_key' => static::DEFAULT_SITE_KEY,
            'contact_phone' => config('business.phone'),
            'contact_whatsapp' => config('business.whatsapp', config('business.phone')),
            'hero_title' => 'MikroTik Kenya – Routers, Switches & Networking Equipment',
            'hero_description' => 'Compare genuine MikroTik routers, switches, access points and wireless systems, with current prices, specifications, stock availability and fast delivery across Kenya.',
            'why_choose_title' => 'Why Buy MikroTik Equipment From Us?',
            'why_choose_intro' => 'Compare practical RouterOS hardware for homes, offices, ISPs and enterprise networks from a catalogue built around Kenyan networking needs.',
            'why_choose_items' => self::defaultWhyChooseItems(),
            'testimonials_badge' => 'Testimonials',
            'testimonials_title' => 'Customer Feedback',
            'testimonials_intro' => 'Feedback from customers can be managed from the admin panel when genuine testimonials are available.',
            'testimonial_items' => self::defaultTestimonialItems(),
            'faq_badge' => 'FAQ',
            'faq_title' => 'MikroTik Buying Questions',
            'faq_intro' => 'Answers to common questions about MikroTik prices, stock, delivery and product selection in Kenya.',
            'faq_items' => self::defaultFaqItems(),
            'content_badge' => 'MikroTik Kenya Guide',
            'content_title' => 'MikroTik Kenya: RouterOS Hardware for Homes, Offices and ISPs',
            'content_intro' => 'Explore MikroTik products for routing, switching, wireless access, LTE backup and network management.',
            'content_body' => self::defaultContentBody(),
        ]);
    }

    public function heroImageUrl(): ?string
    {
        if (! $this->hero_image_path) {
            return null;
        }

        return asset($this->hero_image_path);
    }

    public function siteLogoUrl(): ?string
    {
        if (! $this->site_logo_path) {
            return null;
        }

        return asset($this->site_logo_path);
    }

    public function contactPhone(): ?string
    {
        return $this->fallbackNullableText($this->contact_phone, config('business.phone'));
    }

    public function contactWhatsApp(): ?string
    {
        return $this->fallbackNullableText($this->contact_whatsapp, config('business.whatsapp', config('business.phone')));
    }

    public function whyChooseTitle(): string
    {
        return $this->fallbackText($this->why_choose_title, static::defaultContent()->why_choose_title);
    }

    public function whyChooseIntro(): ?string
    {
        return $this->fallbackNullableText($this->why_choose_intro, static::defaultContent()->why_choose_intro);
    }

    public function whyChooseItems(): array
    {
        return $this->normalizeItems($this->why_choose_items, ['title', 'description'], self::defaultWhyChooseItems());
    }

    public function testimonialsBadge(): ?string
    {
        return $this->fallbackNullableText($this->testimonials_badge, static::defaultContent()->testimonials_badge);
    }

    public function testimonialsTitle(): string
    {
        return $this->fallbackText($this->testimonials_title, static::defaultContent()->testimonials_title);
    }

    public function testimonialsIntro(): ?string
    {
        return $this->fallbackNullableText($this->testimonials_intro, static::defaultContent()->testimonials_intro);
    }

    public function testimonialItems(): array
    {
        return $this->normalizeItems($this->testimonial_items, ['quote', 'name', 'role'], self::defaultTestimonialItems());
    }

    public function faqBadge(): ?string
    {
        return $this->fallbackNullableText($this->faq_badge, static::defaultContent()->faq_badge);
    }

    public function faqTitle(): string
    {
        return $this->fallbackText($this->faq_title, static::defaultContent()->faq_title);
    }

    public function faqIntro(): ?string
    {
        return $this->fallbackNullableText($this->faq_intro, static::defaultContent()->faq_intro);
    }

    public function faqItems(): array
    {
        return $this->normalizeItems($this->faq_items, ['question', 'answer'], self::defaultFaqItems());
    }

    public function contentBadge(): ?string
    {
        return $this->fallbackNullableText($this->content_badge, static::defaultContent()->content_badge);
    }

    public function contentTitle(): string
    {
        return $this->fallbackText($this->content_title, static::defaultContent()->content_title);
    }

    public function contentIntro(): ?string
    {
        return $this->fallbackNullableText($this->content_intro, static::defaultContent()->content_intro);
    }

    public function contentBody(): string
    {
        $html = trim((string) $this->content_body);

        return $html !== '' ? $html : (string) static::defaultContent()->content_body;
    }

    /**
     * @return array<int>
     */
    public function featuredProductIds(): array
    {
        if (! is_array($this->featured_product_ids)) {
            return [];
        }

        return array_values(array_unique(array_filter(
            array_map('intval', $this->featured_product_ids),
            fn (int $id): bool => $id > 0
        )));
    }

    private function fallbackText(mixed $value, string $default): string
    {
        $text = $this->cleanText($value);

        return $text !== null ? $text : $default;
    }

    private function fallbackNullableText(mixed $value, ?string $default): ?string
    {
        $text = $this->cleanText($value);

        return $text !== null ? $text : $default;
    }

    private function cleanText(mixed $value): ?string
    {
        $text = static::plainText($value);

        return $text !== '' ? $text : null;
    }

    private static function plainText(mixed $value): string
    {
        return trim(preg_replace('/\s+/u', ' ', strip_tags((string) $value)) ?? '');
    }

    /**
     * @param  array<int, string>  $keys
     * @param  array<int, array<string, string>>  $defaults
     * @return array<int, array<string, string>>
     */
    private function normalizeItems(mixed $items, array $keys, array $defaults): array
    {
        if (! is_array($items)) {
            return $defaults;
        }

        $normalized = [];

        foreach ($items as $item) {
            if (! is_array($item)) {
                continue;
            }

            $row = [];
            foreach ($keys as $key) {
                $text = $this->cleanText($item[$key] ?? null);
                if ($text === null) {
                    continue 2;
                }

                $row[$key] = Str::limit($text, $key === 'quote' || $key === 'answer' ? 1200 : 220, '');
            }

            $normalized[] = $row;
        }

        return $normalized !== [] ? $normalized : $defaults;
    }

    /**
     * @return array<int, array<string, string>>
     */
    private static function defaultWhyChooseItems(): array
    {
        return [
            ['title' => 'Current Catalogue Prices', 'description' => 'Product pages show prices from the store catalogue instead of static SEO copy.'],
            ['title' => 'RouterOS-Focused Selection', 'description' => 'Browse routers, switches, wireless systems, LTE devices and accessories by practical network use.'],
            ['title' => 'Product-Level Details', 'description' => 'Review SKU, stock status, category, use cases and technical guidance before purchase.'],
            ['title' => 'Quotation Friendly', 'description' => 'Business buyers can use product pages as a starting point for larger networking enquiries.'],
            ['title' => 'Delivery Information', 'description' => 'Delivery options are confirmed during checkout or enquiry based on product availability and destination.'],
            ['title' => 'Configuration Guidance', 'description' => 'Product pages include RouterOS and compatibility notes where available.'],
        ];
    }

    /**
     * @return array<int, array<string, string>>
     */
    private static function defaultTestimonialItems(): array
    {
        return [
            [
                'quote' => 'Add genuine customer feedback from completed orders or verified support interactions in the admin panel.',
                'name' => 'Customer feedback',
                'role' => 'Managed from admin',
            ],
            [
                'quote' => 'Do not publish ratings or reviews unless they come from real customers and can be supported by business records.',
                'name' => 'Review policy',
                'role' => 'Verified reviews only',
            ],
            [
                'quote' => 'Use this section for real installation, procurement or support feedback once available.',
                'name' => 'Trust signals',
                'role' => 'Real customer proof',
            ],
        ];
    }

    /**
     * @return array<int, array<string, string>>
     */
    private static function defaultFaqItems(): array
    {
        return [
            [
                'question' => 'Are prices on the website current?',
                'answer' => 'Product prices are generated from the store catalogue and should update when the admin changes a product price.',
            ],
            [
                'question' => 'Can I compare MikroTik routers before buying?',
                'answer' => 'Use category pages and product pages to compare price, stock status, SKU, category and recommended applications.',
            ],
            [
                'question' => 'Do product pages show stock status?',
                'answer' => 'Yes. Each product page shows whether the product is currently listed as available or out of stock.',
            ],
            [
                'question' => 'Can businesses request quotations?',
                'answer' => 'Business quotation availability should be confirmed through the contact details configured by the site owner.',
            ],
        ];
    }

    private static function defaultContentBody(): string
    {
        return implode('', [
            '<h2>MikroTik networking equipment in Kenya</h2>',
            '<p>MikroTik routers, switches and wireless systems are used for home internet, office networks, ISP deployments, hotspot management, VPNs, firewalling and fibre uplinks.</p>',
            '<p>Use the catalogue to compare current prices, stock status, SKUs and product categories before choosing a RouterOS device.</p>',
            '<h3>Where MikroTik fits best</h3>',
            '<ul>',
            '<li>Homes and small offices that need reliable routing and Wi-Fi management.</li>',
            '<li>Businesses that need firewall, VPN, VLAN and multi-WAN RouterOS features.</li>',
            '<li>ISPs and installers building outdoor wireless, fibre and aggregation networks.</li>',
            '</ul>',
            '<h3>What to Consider Before Buying</h3>',
            '<p>Check the number of Ethernet ports, throughput requirements, PoE support, SFP or SFP+ uplinks, wireless bands, LTE or 5G needs, mounting options and RouterOS feature requirements.</p>',
            '<p>For larger networks, confirm compatibility with your switches, access points, power setup and internet handoff before purchase.</p>',
        ]);
    }
}
