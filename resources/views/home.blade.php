@extends('layouts.app')

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
                       href="{{ route('home', ['category' => $category->id]) }}">
                        {{ $category->name }}
                    </a>
                </li>
            @endforeach
        </ul>
    </aside>

    <div class="home-main">
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
                        @auth
                            <form method="post" action="{{ route('cart.add', $product) }}">
                                @csrf
                                <input type="hidden" name="quantity" value="1">
                                <button type="submit">Add to Cart</button>
                            </form>
                        @else
                            <a class="button-link" href="{{ route('login') }}">Login to Buy</a>
                        @endauth
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
