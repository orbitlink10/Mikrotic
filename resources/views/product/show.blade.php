@extends('layouts.app')

@php
    $productImageFallback = \App\Support\ProductImageCatalog::placeholderUrl();
    $uploadedProductImage = \App\Support\ProductImageCatalog::uploadedUrlFor($product->name, $product->slug);
    $officialProductImage = \App\Support\ProductImageCatalog::officialUrlFor($product->name);
    $descriptionHtml = \App\Support\ProductContent::sanitizeRichText($product->description)
        ?: '<p>No description available.</p>';
    $productMetaDescription = $product->meta_description
        ?: \App\Support\ProductContent::excerpt($product->description, 160);
    $galleryImages = $product->images
        ->map(fn ($image) => $image->publicUrl())
        ->filter()
        ->values();
    if ($galleryImages->isEmpty() && $uploadedProductImage) {
        $galleryImages = collect([$uploadedProductImage]);
    }
    if ($galleryImages->isEmpty()) {
        $galleryImages = collect([$officialProductImage ?: $productImageFallback]);
    }

    $primaryImage = $galleryImages->first();
    $imageErrorFallback = $primaryImage !== $uploadedProductImage && $uploadedProductImage
        ? $uploadedProductImage
        : ($primaryImage !== $officialProductImage && $officialProductImage ? $officialProductImage : $productImageFallback);
    $currentPrice = (float) $product->price;
    $compareAtPrice = (float) ($product->compare_at_price ?? 0);
    $hasDiscount = $compareAtPrice > $currentPrice && $compareAtPrice > 0;
    $discountPercent = $hasDiscount
        ? (int) round((($compareAtPrice - $currentPrice) / $compareAtPrice) * 100)
        : null;
    $availabilityLabel = $product->stock > 0 ? 'IN STOCK' : 'OUT OF STOCK';
    $availabilityClass = $product->stock > 0 ? 'is-available' : 'is-unavailable';
    $productSeoTitle = \App\Support\SeoMetadata::productTitle($product);
    $productMetaDescription = \App\Support\SeoMetadata::productDescription($product);
    $summary = trim((string) $productMetaDescription);
    $productCanonicalUrl = \App\Support\SeoMetadata::canonicalOverride($product)
        ?: \App\Support\CanonicalUrl::route('product.show', $product);
    $productBrand = \App\Support\ProductSeo::brand($product);
    $productModel = \App\Support\ProductSeo::model($product);
    $productSpecs = \App\Support\ProductSeo::specs($product);
    $productUseCases = \App\Support\ProductSeo::useCases($product);
    $productApplications = \App\Support\ProductSeo::applications($product);
    $productBoxItems = \App\Support\ProductSeo::whatsInBox($product);
    $productFaqItems = \App\Support\ProductSeo::faqs($product);
    $chooseAnotherModel = \App\Support\ProductSeo::chooseAnotherModel($product);
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

    $breadcrumbItems = [
        ['name' => 'Home', 'url' => \App\Support\CanonicalUrl::route('home')],
    ];
    if ($product->category) {
        $breadcrumbItems[] = ['name' => $product->category->name, 'url' => \App\Support\CanonicalUrl::route('category.show', $product->category)];
    }
    $breadcrumbItems[] = ['name' => $product->name, 'url' => $productCanonicalUrl];
    $productSchema = \App\Support\StructuredData::product(
        $product,
        $galleryImages->map(fn ($image) => \App\Support\CanonicalUrl::absoluteAsset($image))->all(),
        $productMetaDescription,
        $productCanonicalUrl
    );
    $breadcrumbSchema = \App\Support\StructuredData::breadcrumbs($breadcrumbItems);
    $faqSchema = \App\Support\StructuredData::faq($productFaqItems);
@endphp

@section('title', $productSeoTitle)
@section('meta_description', $productMetaDescription)
@section('canonical_url', $productCanonicalUrl)
@section('og_type', 'product')
@section('og_title', \App\Support\SeoMetadata::openGraphTitle($product, $productSeoTitle))
@section('og_description', \App\Support\SeoMetadata::openGraphDescription($product, $productMetaDescription))
@section('og_image', \App\Support\SeoMetadata::openGraphImage($product, $primaryImage))
@if(\App\Support\SeoMetadata::robots($product))
    @section('robots', \App\Support\SeoMetadata::robots($product))
@endif

@push('head')
    <script type="application/ld+json">@json($productSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)</script>
    <script type="application/ld+json">@json($breadcrumbSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)</script>
    @if($faqSchema)
        <script type="application/ld+json">@json($faqSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)</script>
    @endif
@endpush

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
                    alt="{{ $productBrand === 'MikroTik' ? 'MikroTik ' . $productModel : $product->name }}"
                    class="product-gallery-main-image"
                    data-product-main-image
                    width="900"
                    height="680"
                    fetchpriority="high"
                    decoding="async"
                    onerror="this.onerror=null;this.src='{{ $imageErrorFallback }}';"
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
                            <img
                                src="{{ $galleryImage }}"
                                alt="{{ $productBrand === 'MikroTik' ? 'MikroTik ' . $productModel : $product->name }} thumbnail {{ $index + 1 }}"
                                width="120"
                                height="90"
                                loading="lazy"
                                decoding="async"
                                onerror="this.onerror=null;this.src='{{ $imageErrorFallback }}';"
                            >
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

            <div class="product-identity-row">
                <span>Brand: {{ $productBrand }}</span>
                <span>Model: {{ $productModel }}</span>
                <span>SKU: {{ $product->sku }}</span>
            </div>

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
                            <a class="product-primary-cta" href="{{ route('login') }}">Add to Cart</a>
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
        </div>
    </section>

    <section class="product-seo-grid">
        <article class="product-info-panel">
            <h2>Who is this product best for?</h2>
            <ul>
                @foreach($productUseCases as $useCase)
                    <li>{{ $useCase }}</li>
                @endforeach
            </ul>
        </article>

        @if($chooseAnotherModel)
            <article class="product-info-panel">
                <h2>When should you choose another MikroTik model?</h2>
                <p>{{ $chooseAnotherModel }}</p>
            </article>
        @endif

        <article class="product-info-panel">
            <h2>Key specifications</h2>
            <dl class="product-spec-table">
                @foreach($productSpecs as $specLabel => $specValue)
                    <div>
                        <dt>{{ $specLabel }}</dt>
                        <dd>{{ $specValue }}</dd>
                    </div>
                @endforeach
            </dl>
        </article>

        <article class="product-info-panel">
            <h2>Recommended applications</h2>
            <ul>
                @foreach($productApplications as $application)
                    <li>{{ $application }}</li>
                @endforeach
            </ul>
        </article>

        <article class="product-info-panel">
            <h2>Compatibility</h2>
            <p>{{ \App\Support\ProductSeo::compatibility($product) }}</p>
        </article>

        <article class="product-info-panel">
            <h2>Power requirements</h2>
            <p>{{ \App\Support\ProductSeo::powerRequirements($product) }}</p>
        </article>

        <article class="product-info-panel">
            <h2>What's in the box?</h2>
            <ul>
                @foreach($productBoxItems as $boxItem)
                    <li>{{ $boxItem }}</li>
                @endforeach
            </ul>
        </article>

        <article class="product-info-panel">
            <h2>Warranty, delivery and payment</h2>
            <dl class="product-spec-table">
                <div>
                    <dt>Warranty</dt>
                    <dd>{{ \App\Support\ProductSeo::warrantyInfo($product) }}</dd>
                </div>
                <div>
                    <dt>Delivery</dt>
                    <dd>{{ \App\Support\ProductSeo::deliveryInfo($product) }}</dd>
                </div>
                <div>
                    <dt>Payment</dt>
                    <dd>{{ \App\Support\ProductSeo::paymentInfo($product) }}</dd>
                </div>
            </dl>
        </article>
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

    @if($productFaqItems !== [])
        <section class="product-tabs-shell product-faq-shell">
            <h2>FAQs about {{ $productModel }}</h2>
            <div class="faq-list">
                @foreach($productFaqItems as $item)
                    <details class="faq-item" @if($loop->first) open @endif>
                        <summary>{{ $item['question'] }}</summary>
                        <p>{{ $item['answer'] }}</p>
                    </details>
                @endforeach
            </div>
        </section>
    @endif

    @if(!empty($comparisonLinks))
        <section class="product-tabs-shell product-link-panel">
            <h2>Useful comparisons</h2>
            <nav class="category-hub-links" aria-label="MikroTik product comparisons">
                @foreach($comparisonLinks as $comparisonLink)
                    <a href="{{ $comparisonLink['url'] }}">{{ $comparisonLink['label'] }}</a>
                @endforeach
            </nav>
        </section>
    @endif

    @if($relatedCategories->isNotEmpty())
        <section class="product-tabs-shell product-link-panel">
            <h2>Related MikroTik categories</h2>
            <nav class="category-hub-links" aria-label="Related MikroTik categories">
                @foreach($relatedCategories as $relatedCategory)
                    <a href="{{ route('category.show', $relatedCategory) }}">{{ $relatedCategory->name }}</a>
                @endforeach
            </nav>
        </section>
    @endif

    @if($relatedProducts->isNotEmpty())
        <section class="product-tabs-shell product-related-shell">
            <h2>Related products</h2>
            <div class="products-grid">
                @foreach($relatedProducts as $relatedProduct)
                    @include('partials.product-card', ['product' => $relatedProduct, 'productImageFallback' => $productImageFallback])
                @endforeach
            </div>
        </section>
    @endif
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
