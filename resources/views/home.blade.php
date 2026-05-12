@extends('layouts.app')

@php
    $catalogTitle = $currentCategory
        ? $currentCategory->name . ' | ' . config('app.name', 'Mikrotik Kenya')
        : config('app.name', 'Mikrotik Kenya');
    $catalogMetaDescription = $currentCategory?->meta_description
        ?: ($currentCategory ? \App\Support\ProductContent::excerpt($currentCategory->description, 160) : 'Browse products from our multi-vendor marketplace.');
@endphp

@section('title', $catalogTitle)
@section('meta_description', $catalogMetaDescription)

@section('content')
<section class="home-layout">
    <aside class="category-sidebar">
        <h3>Categories</h3>
        <ul>
            <li>
                <a class="{{ !$selectedCategory ? 'active' : '' }}" href="{{ route('home') }}">All Products</a>
            </li>
            @foreach($categories as $category)
                <li>
                    <a class="{{ $selectedCategory === $category->id ? 'active' : '' }}"
                       href="{{ route('category.show', $category) }}">
                        {{ $category->name }}
                    </a>
                </li>
            @endforeach
        </ul>
    </aside>

    <div class="home-main">
        @if($search !== '')
            <section class="panel catalog-search-summary">
                <p class="catalog-search-eyebrow">Search results</p>
                <h1>Results for "{{ $search }}"</h1>
                <p>
                    {{ $products->total() }} product{{ $products->total() === 1 ? '' : 's' }} found.
                    @if($products->total() === 0)
                        Try a different product name, SKU, or category.
                    @endif
                </p>
            </section>
        @else
            <div
                class="hero-banner"
                @if($homepageContent->heroImageUrl())
                    style="background-image: linear-gradient(120deg, rgba(198, 31, 31, 0.82), rgba(234, 88, 12, 0.72)), url('{{ $homepageContent->heroImageUrl() }}'); background-size: cover; background-position: center;"
                @endif
            >
                <div>
                    <h1>{{ $homepageContent->hero_title }}</h1>
                    <p>{{ $homepageContent->hero_description }}</p>
                </div>
            </div>
        @endif

        <section class="product-grid">
            @forelse($products as $product)
                @php
                    $image = optional($product->images->firstWhere('is_primary', true) ?? $product->images->first())->image_url
                        ?? 'https://via.placeholder.com/480x360?text=Product';
                @endphp
                <article class="product-card">
                    <a class="product-image-wrap" href="{{ route('product.show', $product) }}">
                        <img src="{{ $image }}" alt="{{ $product->name }}">
                    </a>
                    <div class="product-body">
                        <h4><a href="{{ route('product.show', $product) }}">{{ $product->name }}</a></h4>
                        <p class="vendor-name">{{ $product->vendor->shop_name }}</p>
                        <p class="price">KSh {{ number_format((float) $product->price, 2) }}</p>
                        <a class="button-link" href="{{ route('product.show', $product) }}">View</a>
                    </div>
                </article>
            @empty
                <p class="empty">No products found.</p>
            @endforelse
        </section>

        @if($products->hasPages())
            <div class="pager">
                @if($products->onFirstPage())
                    <span class="pager-link disabled">Previous</span>
                @else
                    <a class="pager-link" href="{{ $products->previousPageUrl() }}">Previous</a>
                @endif
                <span>Page {{ $products->currentPage() }} of {{ $products->lastPage() }}</span>
                @if($products->hasMorePages())
                    <a class="pager-link" href="{{ $products->nextPageUrl() }}">Next</a>
                @else
                    <span class="pager-link disabled">Next</span>
                @endif
            </div>
        @endif
    </div>
</section>
@endsection
