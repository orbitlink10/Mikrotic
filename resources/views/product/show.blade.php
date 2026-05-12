@extends('layouts.app')

@php
    $descriptionHtml = \App\Support\ProductContent::sanitizeRichText($product->description)
        ?: '<p>No description available.</p>';
    $productMetaDescription = $product->meta_description
        ?: \App\Support\ProductContent::excerpt($product->description, 160);
    $galleryImages = $product->images->pluck('image_url')->filter()->values();
    if ($galleryImages->isEmpty()) {
        $galleryImages = collect(['https://via.placeholder.com/960x960?text=Product']);
    }

    $primaryImage = $galleryImages->first();
    $currentPrice = (float) $product->price;
    $compareAtPrice = (float) ($product->compare_at_price ?? 0);
    $hasDiscount = $compareAtPrice > $currentPrice && $compareAtPrice > 0;
    $discountPercent = $hasDiscount
        ? (int) round((($compareAtPrice - $currentPrice) / $compareAtPrice) * 100)
        : null;
    $availabilityLabel = $product->stock > 0 ? 'IN STOCK' : 'OUT OF STOCK';
    $availabilityClass = $product->stock > 0 ? 'is-available' : 'is-unavailable';
    $summary = trim((string) ($product->meta_description ?: \App\Support\ProductContent::excerpt($product->description, 280)));
    $vendorPhoneDigits = preg_replace('/\D+/', '', (string) $product->vendor->phone);
    if ($vendorPhoneDigits !== '') {
        if (str_starts_with($vendorPhoneDigits, '0')) {
            $vendorPhoneDigits = '254' . substr($vendorPhoneDigits, 1);
        } elseif (!str_starts_with($vendorPhoneDigits, '254') && strlen($vendorPhoneDigits) === 9) {
            $vendorPhoneDigits = '254' . $vendorPhoneDigits;
        }
    }

    $whatsAppUrl = $vendorPhoneDigits !== ''
        ? 'https://wa.me/' . $vendorPhoneDigits . '?text=' . rawurlencode('Hello, I would like to inquire about ' . $product->name . '.')
        : null;
@endphp

@section('title', $product->name . ' | ' . config('app.name', 'Mikrotik Kenya'))
@section('meta_description', $productMetaDescription)

@section('content')
<div class="product-page">
    <nav class="product-breadcrumbs" aria-label="Breadcrumb">
        <a href="{{ route('home') }}">Home</a>
        @if($product->category)
            <span>/</span>
            <a href="{{ route('category.show', $product->category) }}">{{ $product->category->name }}</a>
        @endif
        <span>/</span>
        <span>{{ $product->name }}</span>
    </nav>

    <section class="product-showcase">
        <div class="product-gallery-card" data-product-gallery>
            <div class="product-gallery-stage">
                <img
                    src="{{ $primaryImage }}"
                    alt="{{ $product->name }}"
                    class="product-gallery-main-image"
                    data-product-main-image
                >
            </div>

            @if($galleryImages->count() > 1)
                <div class="product-gallery-thumbs" data-product-gallery-thumbs>
                    @foreach($galleryImages as $index => $galleryImage)
                        <button
                            type="button"
                            class="product-gallery-thumb {{ $index === 0 ? 'is-active' : '' }}"
                            data-product-image="{{ $galleryImage }}"
                            data-product-alt="{{ $product->name }} image {{ $index + 1 }}"
                            aria-label="View image {{ $index + 1 }} of {{ $product->name }}"
                        >
                            <img src="{{ $galleryImage }}" alt="{{ $product->name }} thumbnail {{ $index + 1 }}">
                        </button>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="product-summary-card">
            <div class="product-summary-topline">
                <p class="product-page-category">
                    @if($product->category)
                        <a href="{{ route('category.show', $product->category) }}">{{ $product->category->name }}</a>
                    @else
                        General
                    @endif
                </p>
                <span class="product-stock-badge {{ $availabilityClass }}">{{ $availabilityLabel }}</span>
            </div>

            <h1 class="product-page-title">{{ $product->name }}</h1>

            <div class="product-price-row">
                <span class="product-current-price">KSh {{ number_format($currentPrice, 2) }}</span>
                @if($hasDiscount)
                    <span class="product-compare-price">KSh {{ number_format($compareAtPrice, 2) }}</span>
                    <span class="product-discount-pill">{{ $discountPercent }}% OFF</span>
                @endif
            </div>

            @if($summary !== '')
                <p class="product-summary-copy product-summary-copy--meta">{{ $summary }}</p>
            @endif

            <div class="product-benefit-row product-benefit-row--services">
                <span class="product-benefit-chip">Fast delivery</span>
                <span class="product-benefit-chip">Warranty support</span>
                <span class="product-benefit-chip">Expert help</span>
            </div>

            <div class="product-summary-divider" aria-hidden="true"></div>

            <div class="product-purchase-card">
                @if($product->stock > 0)
                    @auth
                        <form class="product-purchase-form" method="post" action="{{ route('cart.add', $product) }}">
                            @csrf
                            <div class="product-quantity-block">
                                <span class="product-quantity-label">Quantity</span>
                                <div class="product-quantity-picker">
                                    <button type="button" class="product-qty-control" data-qty-adjust="-1" aria-label="Decrease quantity">-</button>
                                    <input
                                        type="number"
                                        name="quantity"
                                        value="1"
                                        min="1"
                                        max="{{ $product->stock }}"
                                        class="product-quantity-input"
                                        data-qty-input
                                    >
                                    <button type="button" class="product-qty-control" data-qty-adjust="1" aria-label="Increase quantity">+</button>
                                </div>
                            </div>

                            <div class="product-cta-row">
                                <button type="submit" name="redirect" value="checkout" class="product-primary-cta">Buy Now</button>
                                <button type="submit" name="redirect" value="cart" class="product-secondary-cta">Add to Cart</button>
                                @if($whatsAppUrl)
                                    <a class="product-whatsapp-cta" href="{{ $whatsAppUrl }}" target="_blank" rel="noopener noreferrer">WhatsApp</a>
                                @endif
                            </div>
                        </form>
                    @else
                        <div class="product-cta-row">
                            <a class="product-primary-cta" href="{{ route('login') }}">Login to Buy</a>
                            @if($whatsAppUrl)
                                <a class="product-whatsapp-cta" href="{{ $whatsAppUrl }}" target="_blank" rel="noopener noreferrer">WhatsApp</a>
                            @endif
                        </div>
                    @endauth
                @else
                    <div class="product-cta-row">
                        <button type="button" class="product-primary-cta" disabled>Out of Stock</button>
                        @if($whatsAppUrl)
                            <a class="product-whatsapp-cta" href="{{ $whatsAppUrl }}" target="_blank" rel="noopener noreferrer">Ask on WhatsApp</a>
                        @endif
                    </div>
                @endif
            </div>

            <div class="product-summary-divider" aria-hidden="true"></div>

            <div class="product-availability-row">
                <span class="product-availability-label">Availability:</span>
                <span class="product-availability-pill {{ $availabilityClass }}">
                    {{ $product->stock > 0 ? 'AVAILABLE IN STORE' : 'OUT OF STOCK' }}
                </span>
            </div>

            <div class="product-store-meta">
                <span><strong>Store:</strong> {{ $product->vendor->shop_name }}</span>
                <span><strong>SKU:</strong> {{ $product->sku }}</span>
                @if($product->vendor->address)
                    <span><strong>Location:</strong> {{ $product->vendor->address }}</span>
                @endif
            </div>
        </div>
    </section>

    <section class="product-tabs-shell" data-product-tabs>
        <div class="product-tabs" role="tablist" aria-label="Product information tabs">
            <button type="button" class="product-tab-button is-active" data-tab-target="details" role="tab" aria-selected="true">Product details</button>
            <button type="button" class="product-tab-button" data-tab-target="information" role="tab" aria-selected="false">Additional information</button>
            <button type="button" class="product-tab-button" data-tab-target="reviews" role="tab" aria-selected="false">Reviews (0)</button>
        </div>

        <div class="product-tab-panel is-active" data-tab-panel="details" role="tabpanel">
            <div class="rich-content product-description-content">{!! $descriptionHtml !!}</div>
        </div>

        <div class="product-tab-panel" data-tab-panel="information" role="tabpanel" hidden>
            <div class="product-info-grid">
                <div class="product-info-item">
                    <span>Product</span>
                    <strong>{{ $product->name }}</strong>
                </div>
                <div class="product-info-item">
                    <span>Category</span>
                    <strong>{{ $product->category?->name ?? 'General' }}</strong>
                </div>
                <div class="product-info-item">
                    <span>SKU</span>
                    <strong>{{ $product->sku }}</strong>
                </div>
                <div class="product-info-item">
                    <span>Price</span>
                    <strong>KSh {{ number_format($currentPrice, 2) }}</strong>
                </div>
                @if($hasDiscount)
                    <div class="product-info-item">
                        <span>Original price</span>
                        <strong>KSh {{ number_format($compareAtPrice, 2) }}</strong>
                    </div>
                @endif
                <div class="product-info-item">
                    <span>Stock</span>
                    <strong>{{ $product->stock > 0 ? 'Available in store' : 'Out of stock' }}</strong>
                </div>
                <div class="product-info-item">
                    <span>Vendor</span>
                    <strong>{{ $product->vendor->shop_name }}</strong>
                </div>
                @if($product->vendor->address)
                    <div class="product-info-item">
                        <span>Location</span>
                        <strong>{{ $product->vendor->address }}</strong>
                    </div>
                @endif
            </div>
        </div>

        <div class="product-tab-panel" data-tab-panel="reviews" role="tabpanel" hidden>
            <h2>Reviews</h2>
            <p class="product-reviews-empty">Reviews are not available yet for this product.</p>
        </div>
    </section>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const defaultProductAlt = @json($product->name);
    const gallery = document.querySelector('[data-product-gallery]');
    if (gallery) {
        const mainImage = gallery.querySelector('[data-product-main-image]');
        const thumbs = gallery.querySelectorAll('[data-product-image]');

        thumbs.forEach(function (thumb) {
            thumb.addEventListener('click', function () {
                thumbs.forEach(function (button) {
                    button.classList.remove('is-active');
                });

                thumb.classList.add('is-active');
                if (mainImage) {
                    mainImage.src = thumb.getAttribute('data-product-image') || '';
                    mainImage.alt = thumb.getAttribute('data-product-alt') || defaultProductAlt;
                }
            });
        });
    }

    const tabButtons = document.querySelectorAll('[data-tab-target]');
    const tabPanels = document.querySelectorAll('[data-tab-panel]');

    tabButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            const target = button.getAttribute('data-tab-target');

            tabButtons.forEach(function (tabButton) {
                tabButton.classList.remove('is-active');
                tabButton.setAttribute('aria-selected', 'false');
            });

            tabPanels.forEach(function (panel) {
                const isMatch = panel.getAttribute('data-tab-panel') === target;
                panel.classList.toggle('is-active', isMatch);
                panel.hidden = !isMatch;
            });

            button.classList.add('is-active');
            button.setAttribute('aria-selected', 'true');
        });
    });

    const quantityInput = document.querySelector('[data-qty-input]');
    const quantityControls = document.querySelectorAll('[data-qty-adjust]');

    quantityControls.forEach(function (control) {
        control.addEventListener('click', function () {
            if (!quantityInput) {
                return;
            }

            const step = Number(control.getAttribute('data-qty-adjust') || '0');
            const min = Number(quantityInput.getAttribute('min') || '1');
            const max = Number(quantityInput.getAttribute('max') || '1');
            const current = Number(quantityInput.value || min);
            const nextValue = Math.min(max, Math.max(min, current + step));
            quantityInput.value = String(nextValue);
        });
    });
});
</script>
@endsection
