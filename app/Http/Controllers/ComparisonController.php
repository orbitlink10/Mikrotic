<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Support\CanonicalUrl;
use Illuminate\View\View;

class ComparisonController extends Controller
{
    /**
     * @var array<string, array{0: string, 1: string, 2: string}>
     */
    private const COMPARISONS = [
        'rb760igs-vs-rb750gr3' => ['RB760iGS', 'RB750Gr3', 'RB760iGS vs RB750Gr3'],
        'rb4011-vs-rb5009' => ['RB4011', 'RB5009', 'RB4011 vs RB5009'],
        'l009uigs-rm-vs-l009uigs-2haxd-in' => ['L009UiGS-RM', 'L009UiGS-2HaxD-IN', 'L009UiGS-RM vs L009UiGS-2HaxD-IN'],
        'ccr2004-vs-ccr2116' => ['CCR2004', 'CCR2116', 'CCR2004 vs CCR2116'],
    ];

    public function show(string $comparison): View
    {
        abort_unless(isset(self::COMPARISONS[$comparison]), 404);

        [$left, $right, $title] = self::COMPARISONS[$comparison];
        $products = collect([$left, $right])
            ->map(fn (string $needle): ?Product => $this->findProduct($needle))
            ->filter()
            ->values();

        abort_if($products->count() < 2, 404);

        return view('comparison.show', [
            'comparison' => $comparison,
            'title' => $title,
            'products' => $products,
            'canonicalUrl' => CanonicalUrl::route('comparison.show', $comparison),
        ]);
    }

    private function findProduct(string $needle): ?Product
    {
        $needleSlug = str($needle)->lower()->replace('+', '-')->replace('_', '-')->slug()->toString();

        return Product::query()
            ->with(['vendor', 'category', 'images' => fn ($query) => $query->orderByDesc('is_primary')->orderBy('sort_order')])
            ->active()
            ->where(function ($query) use ($needle, $needleSlug): void {
                $query->where('name', 'like', '%'.$needle.'%')
                    ->orWhere('sku', 'like', '%'.$needle.'%')
                    ->orWhere('slug', 'like', '%'.$needleSlug.'%');
            })
            ->first();
    }
}
