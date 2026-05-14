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
    ];

    protected $casts = [
        'why_choose_items' => 'array',
        'testimonial_items' => 'array',
        'faq_items' => 'array',
    ];

    public static function current(): self
    {
        if (!static::storageReady()) {
            return static::defaultContent();
        }

        return static::query()->where('site_key', static::DEFAULT_SITE_KEY)->first()
            ?? static::defaultContent();
    }

    public static function storageReady(): bool
    {
        $table = (new static())->getTable();

        if (!Schema::hasTable($table)) {
            return false;
        }

        foreach ([
            'site_key',
            'site_logo_path',
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
        ] as $column) {
            if (!Schema::hasColumn($table, $column)) {
                return false;
            }
        }

        return true;
    }

    private static function defaultContent(): self
    {
        return new static([
            'site_key' => static::DEFAULT_SITE_KEY,
            'hero_title' => 'Starlink Kenya | High-Speed Satellite Internet Across Kenya',
            'hero_description' => 'Starlink Kenya offers high-speed satellite internet with affordable packages, hardware, and monthly plans. Stay connected anywhere in Kenya today.',
            'why_choose_title' => 'Why Choose Starlink Kenya?',
            'why_choose_intro' => 'Get dependable high-speed internet for homes, businesses, schools, farms, and remote sites with a local team that understands the Kenyan market.',
            'why_choose_items' => self::defaultWhyChooseItems(),
            'testimonials_badge' => 'Testimonials',
            'testimonials_title' => 'What Our Clients Say',
            'testimonials_intro' => 'Reliable satellite internet is changing how families, creators, and growing businesses stay connected across Kenya.',
            'testimonial_items' => self::defaultTestimonialItems(),
            'faq_badge' => 'FAQ',
            'faq_title' => 'Frequently Asked Questions',
            'faq_intro' => 'Answers to the most common questions about Starlink hardware, speeds, installation, and support in Kenya.',
            'faq_items' => self::defaultFaqItems(),
            'content_badge' => 'Starlink Kenya Guide',
            'content_title' => 'Starlink Kenya: A Comprehensive Guide to Satellite Internet Connectivity',
            'content_intro' => 'Explore how Starlink is transforming internet access across Kenya, from urban homes and branch offices to rural schools, farms, lodges, and construction sites.',
            'content_body' => self::defaultContentBody(),
        ]);
    }

    public function heroImageUrl(): ?string
    {
        if (!$this->hero_image_path) {
            return null;
        }

        return asset($this->hero_image_path);
    }

    public function siteLogoUrl(): ?string
    {
        if (!$this->site_logo_path) {
            return null;
        }

        return asset($this->site_logo_path);
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
     * @param  mixed  $items
     * @param  array<int, string>  $keys
     * @param  array<int, array<string, string>>  $defaults
     * @return array<int, array<string, string>>
     */
    private function normalizeItems(mixed $items, array $keys, array $defaults): array
    {
        if (!is_array($items)) {
            return $defaults;
        }

        $normalized = [];

        foreach ($items as $item) {
            if (!is_array($item)) {
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
            ['title' => 'Official Reseller', 'description' => 'Authentic Starlink hardware and reliable local guidance.'],
            ['title' => 'Local Delivery', 'description' => 'Fast dispatch in Nairobi with nationwide coordination.'],
            ['title' => 'Professional Installation', 'description' => 'Clean alignment, mounting, and cable routing support.'],
            ['title' => 'Flexible Payments', 'description' => 'Practical options for hardware, setup, and monthly service.'],
            ['title' => 'Warranty Support', 'description' => 'Responsive after-sales help for faults, setup, and replacements.'],
            ['title' => 'Kenya-Based Support', 'description' => 'Talk to a local team that understands your environment.'],
        ];
    }

    /**
     * @return array<int, array<string, string>>
     */
    private static function defaultTestimonialItems(): array
    {
        return [
            [
                'quote' => 'The installation team arrived on time, explained the ideal mounting position, and got us online the same day. The experience felt professional from start to finish.',
                'name' => 'Joan K., Nairobi',
                'role' => 'Homeowner',
            ],
            [
                'quote' => 'Our children now attend online classes without interruptions, and video meetings are finally stable. Starlink has made a visible difference in our day-to-day routine.',
                'name' => 'Samuel O., Meru',
                'role' => 'Parent',
            ],
            [
                'quote' => 'Uploads that used to take forever now finish quickly, which matters a lot for my content work. For creators working outside strong fiber zones, this is a serious upgrade.',
                'name' => 'Victor M., Rongai',
                'role' => 'Content Creator',
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
                'question' => 'What is included in a Starlink kit?',
                'answer' => 'A typical kit includes the dish, router, power supply, mounting hardware, and the cables required for the initial setup.',
            ],
            [
                'question' => 'Do you offer installation support in Kenya?',
                'answer' => 'Yes. Installation support can include site checks, mounting advice, dish alignment, cable routing, and post-install connectivity checks.',
            ],
            [
                'question' => 'Is Starlink suitable for rural homes and remote business sites?',
                'answer' => 'Yes. Starlink is especially useful where fiber is unavailable, unreliable, or too slow to support remote work, CCTV, school access, or business operations.',
            ],
            [
                'question' => 'What speeds should customers expect?',
                'answer' => 'Actual speeds vary by location, weather, and network demand, but Starlink is designed to provide a much stronger broadband experience than many underserved terrestrial options.',
            ],
        ];
    }

    private static function defaultContentBody(): string
    {
        return implode('', [
            '<h2>Introduction: Bridging Kenya&apos;s Digital Divide</h2>',
            '<p>Starlink is opening new connectivity options for homes, schools, hospitality properties, farms, logistics teams, and field operations that sit beyond dependable fiber or fixed wireless coverage.</p>',
            '<p>For many customers, the biggest value is not just headline speed. It is the ability to get stable internet in places where traditional infrastructure is delayed, inconsistent, or simply unavailable.</p>',
            '<h3>Where Starlink Fits Best</h3>',
            '<ul>',
            '<li>Homes that need dependable streaming, remote work, and online learning.</li>',
            '<li>Businesses operating branches, remote offices, warehouses, or project sites.</li>',
            '<li>Schools, clinics, camps, and lodges located far from strong terrestrial networks.</li>',
            '</ul>',
            '<h3>What to Consider Before Buying</h3>',
            '<p>Before installation, it is important to assess line of sight, the best mounting position, power stability, internal Wi-Fi coverage, and how the service will be used across the property.</p>',
            '<p>A proper setup helps customers get the most from the hardware and reduces avoidable service interruptions caused by obstructions or poor equipment placement.</p>',
        ]);
    }
}
