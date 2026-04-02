@extends('layouts.app')

@php
    $descriptionHtml = \App\Support\ProductContent::sanitizeRichText($product->description)
        ?: '<p>No description available.</p>';
    $productMetaDescription = $product->meta_description
        ?: \App\Support\ProductContent::excerpt($product->description, 160);
@endphp

@section('title', $product->name . ' | ' . config('app.name', 'Almar Market'))
@section('meta_description', $productMetaDescription)

@section('content')
@php
    $image = optional($product->images->firstWhere('is_primary', true) ?? $product->images->first())->image_url
        ?? 'https://via.placeholder.com/480x360?text=Product';
@endphp
<section class="product-detail">
    <div class="product-detail-image">
        <img src="{{ $image }}" alt="{{ $product->name }}">
    </div>
    <div class="product-detail-info">
        <p class="product-category">{{ $product->category->name ?? 'General' }}</p>
        <h1>{{ $product->name }}</h1>
        <p class="vendor-name">Sold by {{ $product->vendor->shop_name }}</p>
        <p class="price large">KSh {{ number_format((float) $product->price, 2) }}</p>
        <p class="stock">{{ $product->stock > 0 ? 'In stock' : 'Out of stock' }}</p>
        <div class="description rich-content">{!! $descriptionHtml !!}</div>
        @auth
            <form class="inline-form" method="post" action="{{ route('cart.add', $product) }}">
                @csrf
                <label>
                    Qty
                    <input type="number" name="quantity" min="1" max="{{ $product->stock }}" value="1">
                </label>
                <button type="submit">Add to Cart</button>
            </form>
        @else
            <a class="button-link" href="{{ route('login') }}">Login to Buy</a>
        @endauth
    </div>
</section>
@endsection
