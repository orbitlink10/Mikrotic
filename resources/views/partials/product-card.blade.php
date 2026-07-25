@php
    $productImage = $product->images->firstWhere('is_primary', true) ?? $product->images->first();
    $image = $productImage?->publicUrl()
        ?: \App\Support\ProductImageCatalog::officialUrlFor($product->name)
        ?: $productImageFallback;
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
            alt="{{ $product->name }}"
            loading="lazy"
            decoding="async"
            onerror="this.onerror=null;this.src='{{ $productImageFallback }}';"
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
