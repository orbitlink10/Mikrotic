<?php

namespace App\Support;

use App\Models\Product;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class ProductSeo
{
    public static function brand(Product $product): string
    {
        return self::columnValue($product, 'brand')
            ?: (Str::contains(Str::lower($product->name.' '.$product->category?->name), 'mikrotik') ? 'MikroTik' : config('app.name', 'Mikrotik Kenya'));
    }

    public static function displayName(Product $product): string
    {
        $name = trim(preg_replace('/\s+/u', ' ', $product->name) ?? $product->name);

        if (self::brand($product) === 'MikroTik') {
            $name = preg_replace('/\bmikrotik\b/i', 'MikroTik', $name) ?? $name;
        }

        return $name !== '' ? $name : self::model($product);
    }

    public static function model(Product $product): string
    {
        if ($model = self::columnValue($product, 'model_number')) {
            return $model;
        }

        $name = trim(preg_replace('/\s+/u', ' ', $product->name) ?? $product->name);
        $model = preg_replace('/^mikrotik\s+/i', '', $name) ?? $name;

        return trim($model) !== '' ? trim($model) : $product->sku;
    }

    public static function keyUse(Product $product): string
    {
        if ($keyUse = self::columnValue($product, 'key_use')) {
            return $keyUse;
        }

        $intent = MikrotikSeoCatalog::productIntentSlug($product);

        return match ($intent) {
            'mikrotik-switches' => 'Switching, aggregation and network expansion',
            'mikrotik-access-points' => 'Managed Wi-Fi coverage for homes and offices',
            'mikrotik-wireless' => 'Outdoor wireless links and ISP deployments',
            'mikrotik-lte-5g' => 'LTE/5G internet and backup connectivity',
            'mikrotik-sfp-modules' => 'Fibre uplinks and high-speed interconnects',
            'routeros' => 'RouterOS routing, firewall and network management',
            default => 'Routing for homes, offices, ISPs and branch networks',
        };
    }

    /**
     * @return array<string, string>
     */
    public static function specs(Product $product): array
    {
        $specs = [
            'Model' => self::model($product),
            'Brand' => self::brand($product),
            'SKU' => $product->sku,
            'Category' => $product->category?->name ?? 'MikroTik products',
            'Current price' => 'KSh '.number_format((float) $product->price, 2),
            'Availability' => $product->stock > 0 ? 'In stock' : 'Out of stock',
        ];

        foreach (self::linesFromColumn($product, 'technical_specifications') as $line) {
            if (str_contains($line, ':')) {
                [$key, $value] = array_map('trim', explode(':', $line, 2));
                if ($key !== '' && $value !== '') {
                    $specs[$key] = $value;
                }
            }
        }

        return array_filter($specs, fn (?string $value): bool => trim((string) $value) !== '');
    }

    /**
     * @return array<int, string>
     */
    public static function useCases(Product $product): array
    {
        $custom = self::linesFromColumn($product, 'use_cases');
        if ($custom !== []) {
            return $custom;
        }

        return match (MikrotikSeoCatalog::productIntentSlug($product)) {
            'mikrotik-switches' => ['Office LAN expansion', 'Rack or cabinet switching', 'ISP or branch network aggregation'],
            'mikrotik-access-points' => ['Indoor Wi-Fi coverage', 'Small office wireless networks', 'Managed RouterOS wireless deployments'],
            'mikrotik-wireless' => ['Point-to-point wireless links', 'Outdoor broadband distribution', 'Remote site connectivity'],
            'mikrotik-lte-5g' => ['Backup internet links', 'Remote offices and field sites', 'Mobile broadband where fibre is unavailable'],
            default => ['Home and small office routing', 'Business internet edge routing', 'ISP customer or branch deployments'],
        };
    }

    /**
     * @return array<int, string>
     */
    public static function applications(Product $product): array
    {
        return self::linesFromColumn($product, 'recommended_applications') ?: self::useCases($product);
    }

    public static function compatibility(Product $product): string
    {
        return self::columnValue($product, 'compatibility')
            ?: 'Works with compatible MikroTik RouterOS networks and standard Ethernet networking equipment. Confirm port, power and mounting requirements before purchase.';
    }

    public static function powerRequirements(Product $product): string
    {
        return self::columnValue($product, 'power_requirements')
            ?: 'Check the product label or manufacturer datasheet for exact input voltage, PoE support and power adapter requirements.';
    }

    public static function warrantyInfo(Product $product): string
    {
        return self::columnValue($product, 'warranty_info')
            ?: 'Warranty terms depend on the seller and product condition. Confirm warranty coverage before checkout or quotation approval.';
    }

    public static function deliveryInfo(Product $product): string
    {
        return self::columnValue($product, 'delivery_info')
            ?: 'Delivery options and timelines are confirmed during checkout or direct enquiry based on stock location and destination.';
    }

    public static function paymentInfo(Product $product): string
    {
        return self::columnValue($product, 'payment_info')
            ?: 'Payment options are confirmed at checkout or through the seller before dispatch.';
    }

    public static function chooseAnotherModel(Product $product): ?string
    {
        return self::columnValue($product, 'choose_another_model');
    }

    /**
     * @return array<int, string>
     */
    public static function whatsInBox(Product $product): array
    {
        return self::linesFromColumn($product, 'whats_in_box')
            ?: ['MikroTik '.$product->name.' unit', 'Included accessories as supplied by the seller or manufacturer package'];
    }

    /**
     * @return array<int, array{question: string, answer: string}>
     */
    public static function faqs(Product $product): array
    {
        $custom = self::faqItems($product);
        if ($custom !== []) {
            return $custom;
        }

        return [
            [
                'question' => 'Is '.$product->name.' available in Kenya?',
                'answer' => $product->stock > 0
                    ? $product->name.' is currently listed as available. Stock can change, so confirm availability before placing a large order.'
                    : $product->name.' is currently listed as out of stock. Contact the seller to confirm the next availability date.',
            ],
            [
                'question' => 'What is the current price of '.$product->name.'?',
                'answer' => 'The current listed price is KSh '.number_format((float) $product->price, 2).'. Prices are generated from the product catalogue and may change when inventory is updated.',
            ],
        ];
    }

    /**
     * @return array<int, array{label: string, url: string}>
     */
    public static function comparisonLinks(Product $product): array
    {
        $pairs = [
            ['rb760igs', 'rb750gr3', 'RB760iGS vs RB750Gr3'],
            ['rb4011', 'rb5009', 'RB4011 vs RB5009'],
            ['l009uigs-rm', 'l009uigs-2haxd-in', 'L009UiGS-RM vs L009UiGS-2HaxD-IN'],
            ['ccr2004', 'ccr2116', 'CCR2004 vs CCR2116'],
        ];

        $haystack = Str::lower($product->name.' '.$product->slug.' '.$product->sku);
        $links = [];

        foreach ($pairs as [$left, $right, $label]) {
            if (! Str::contains($haystack, [$left, $right])) {
                continue;
            }

            $otherNeedle = Str::contains($haystack, $left) ? $right : $left;
            $otherProduct = Product::query()
                ->active()
                ->where(function ($query) use ($otherNeedle): void {
                    $query->where('slug', 'like', '%'.$otherNeedle.'%')
                        ->orWhere('name', 'like', '%'.$otherNeedle.'%')
                        ->orWhere('sku', 'like', '%'.$otherNeedle.'%');
                })
                ->first();

            if ($otherProduct) {
                $links[] = [
                    'label' => $label,
                    'url' => route('comparison.show', Str::slug($label)),
                ];
            }
        }

        return $links;
    }

    /**
     * @return array<int, array{question: string, answer: string}>
     */
    private static function faqItems(Product $product): array
    {
        if (! self::columnReady($product, 'faq_items') || ! is_array($product->faq_items)) {
            return [];
        }

        $items = [];
        foreach ($product->faq_items as $item) {
            if (! is_array($item)) {
                continue;
            }

            $question = trim((string) ($item['question'] ?? ''));
            $answer = trim((string) ($item['answer'] ?? ''));

            if ($question !== '' && $answer !== '') {
                $items[] = ['question' => $question, 'answer' => $answer];
            }
        }

        return $items;
    }

    /**
     * @return array<int, string>
     */
    private static function linesFromColumn(Product $product, string $column): array
    {
        $value = self::columnValue($product, $column);
        if (! $value) {
            return [];
        }

        return array_values(array_filter(array_map(
            fn (string $line): string => trim(strip_tags($line)),
            preg_split('/\r\n|\r|\n/', $value) ?: []
        )));
    }

    private static function columnValue(Product $product, string $column): ?string
    {
        if (! self::columnReady($product, $column)) {
            return null;
        }

        $value = trim((string) ($product->{$column} ?? ''));

        return $value !== '' ? $value : null;
    }

    private static function columnReady(Product $product, string $column): bool
    {
        static $cache = [];
        $key = $product->getTable().'.'.$column;

        return $cache[$key] ??= Schema::hasColumn($product->getTable(), $column);
    }
}
