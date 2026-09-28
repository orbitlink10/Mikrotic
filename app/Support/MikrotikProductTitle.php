<?php

namespace App\Support;

use App\Models\Product;
use Illuminate\Support\Str;

/**
 * Title-only rules: do not change display names, catalogue categories or page copy.
 */
class MikrotikProductTitle
{
    public static function appliesTo(Product $product): bool
    {
        $brand = trim((string) $product->brand);

        if ($brand !== '') {
            return strcasecmp($brand, 'MikroTik') === 0;
        }

        $identity = implode(' ', [
            $product->name,
            $product->slug,
            $product->category?->name,
            $product->category?->slug,
            $product->category?->parent?->name,
            $product->category?->parent?->slug,
        ]);

        return (bool) preg_match('/\bmikrotik\b/i', $identity)
            || (bool) preg_match('/^(?:RB\d|CCR\d|CRS\d|CSS\d|L009)/i', trim((string) ($product->model_number ?: $product->name)));
    }

    public static function make(Product $product): string
    {
        $model = self::model($product);

        // Do not truncate: punctuation and complete model identifiers matter.
        return 'MikroTik '.$model.' Price in Kenya | '.self::type($product, $model);
    }

    private static function model(Product $product): string
    {
        $name = preg_replace('/\s+[–—-]\s*$/u', '', (string) $product->name);

        foreach ([$product->model_number, $name, $product->sku] as $value) {
            $model = trim(preg_replace('/\bmikrotik\b\s*/iu', '', (string) $value) ?? '');

            if ($model !== '') {
                return $model;
            }
        }

        return '';
    }

    private static function type(Product $product, string $model): string
    {
        // Match model families before categories. Categories can be broad or wrong,
        // and strings like RB951G and 2.5G must never imply a cellular modem.
        $identity = $model.' '.$product->name;

        if (preg_match('/\b(?:CRS|CSS)\d|\bRB260/i', $model)) {
            return 'Switch';
        }

        if (preg_match('/\bCCR\d/i', $model)) {
            return 'Cloud Core Router';
        }

        if (preg_match('/\b(?:RB4011iGS\+RM|RB5009\S+|RB750Gr3|RB760iGS|RB3011\S+|RB1100\S+|L009UiGS-RM|E50UG|E60iUGS)\b/i', $model)) {
            return 'Gigabit Router';
        }

        if (preg_match('/\b(?:RBPOE|RBGPOE|RBGPOE-CON-HP|RBGESP)\b|\bPoE injector\b/i', $identity)) {
            return preg_match('/\bRBGESP\b/i', $identity) ? 'Surge Protector' : 'PoE Injector';
        }

        if (preg_match('/\bmANTBox\b/i', $identity)) {
            return 'Outdoor Access Point';
        }

        if (preg_match('/\bmANT(?:\d|\b)|\bantenna\b/i', $identity)) {
            return 'Antenna';
        }

        if (preg_match('/\bR11eL?-/i', $model)) {
            return preg_match('/\bR11e(?:L-|-(?:LTE|4G))/i', $model) ? 'LTE Modem' : 'Wireless Module';
        }

        if (preg_match('/^(?:S|XS|Q)[+-].*?(?:DA|BC|AO)/i', $model) || preg_match('/\b(?:DAC|AOC|cable)\b/i', $identity)) {
            return 'Network Cable';
        }

        if (preg_match('/^(?:S|XS|Q)[+-]/i', $model) || preg_match('/\btransceiver\b/i', $identity)) {
            return 'Transceiver Module';
        }

        if (preg_match('/\b(?:5G|LTE\d*|LTE\d*-?Advanced)\b/i', $identity, $cellular)) {
            return strcasecmp($cellular[0], '5G') === 0 ? '5G Router' : 'LTE Router';
        }

        if (preg_match('/\b(?:cAP|wAP|mAP)(?:\b|[A-Z])|\baccess point\b/i', $identity)) {
            return 'Access Point';
        }

        if (preg_match('/\b(?:hAP|hEX|Chateau|Audience)\b/i', $identity, $router)) {
            return strcasecmp($router[0], 'hEX') === 0 ? 'Router' : 'Wireless Router';
        }

        if (preg_match('/\b(?:LHG|SXT\w*|QRT|DISC|Groove\w*|Metal|NetMetal|NetBox|BaseBox|Cube\w*|OmniTIK|Wireless Wire)\b/i', $identity)) {
            return 'Wireless System';
        }

        if (preg_match('/\b(?:RB\d|L009)/i', $model)) {
            return 'Router';
        }

        if (preg_match('/\b(?:RouterOS|CHR|license|licence)\b/i', $identity)) {
            return 'Software';
        }

        $category = $product->category;
        $slug = Str::slug((string) $category?->slug);
        $slug = MikrotikSeoCatalog::targetSlugForLegacy($slug) ?: $slug;

        // A category alone cannot establish LTE/5G capability.
        return match ($slug) {
            MikrotikSeoCatalog::ROUTER_AUTHORITY_SLUG => 'Router',
            'mikrotik-switches' => 'Switch',
            'mikrotik-access-points' => 'Access Point',
            'mikrotik-wireless' => 'Wireless System',
            'mikrotik-sfp-modules' => 'Transceiver Module',
            'mikrotik-antennas' => 'Antenna',
            'mikrotik-accessories' => 'Networking Accessory',
            'routeros' => 'Software',
            default => 'Networking Equipment',
        };
    }
}
