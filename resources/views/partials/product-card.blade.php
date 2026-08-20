@php
    $productImage = $product->images->firstWhere('is_primary', true) ?? $product->images->first();
    $uploadedProductImage = \App\Support\ProductImageCatalog::uploadedUrlFor($product->name, $product->slug);
    $officialProductImage = \App\Support\ProductImageCatalog::officialUrlFor($product->name);
    $image = $productImage?->publicUrl()
        ?: $uploadedProductImage
        ?: $officialProductImage
        ?: $productImageFallback;
    $imageErrorFallback = $image !== $uploadedProductImage && $uploadedProductImage
        ? $uploadedProductImage
        : ($image !== $officialProductImage && $officialProductImage ? $officialProductImage : $productImageFallback);
    $productDescription = \App\Support\ProductContent::excerpt($product->meta_description ?: $product->description, 132);
    $productDescription = $productDescription !== ''
        ? $productDescription
        : $product->name . ' is available in Kenya.';
@endphp

<article class="product-card">
    <a class="product-media-link" href="{{ route('product.show', $product) }}" aria-label="View {{ $product->name }}">
        <img
            class="product-image"
            src="{{ $image }}"
            alt="{{ \App\Support\ProductSeo::brand($product) === 'MikroTik' ? 'MikroTik ' . \App\Support\ProductSeo::model($product) : $product->name }}"
            width="480"
            height="360"
            loading="lazy"
            decoding="async"
            onerror="this.onerror=null;this.src='{{ $imageErrorFallback }}';"
        >
    </a>
    <div class="product-body">
        <h3 class="product-name">
            <a href="{{ route('product.show', $product) }}">{{ $product->name }}</a>
        </h3>
        <p class="product-desc">{{ $productDescription }}</p>
        <div class="product-bottom">
            <span class="price">KES {{ number_format((float) $product->price, 2) }}</span>
            <a class="view-btn" href="{{ route('product.show', $product) }}">View</a>
        </div>
    </div>
</article>
