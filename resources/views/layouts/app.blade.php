<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'Almar Market') }}</title>
    <link rel="stylesheet" href="{{ asset('assets/market.css') }}">
</head>
<body>
@php
    $cartCount = 0;
    if (auth()->check()) {
        $cartCount = \App\Models\CartItem::query()
            ->whereHas('cart', fn ($q) => $q->where('user_id', auth()->id()))
            ->sum('quantity');
    }
@endphp
<header class="top-header">
    <div class="promo-bar">Laravel Multi-Vendor Marketplace (MySQL)</div>
    <div class="nav-wrap">
        <a href="{{ route('home') }}" class="logo">
            <span class="logo-main">ALMAR</span><span class="logo-sub">MARKET</span>
        </a>

        <form class="search-form" method="get" action="{{ route('home') }}">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search products, brands and categories">
            <button type="submit">Search</button>
        </form>

        <nav class="top-links">
            @auth
                <span class="greeting">Hi, {{ auth()->user()->name }}</span>
                @if(auth()->user()->role === 'vendor')
                    <a href="{{ route('vendor.dashboard') }}">Vendor</a>
                @elseif(auth()->user()->role === 'customer')
                    <a href="{{ route('vendor.apply.form') }}">Sell on Almar</a>
                @endif
                @if(auth()->user()->role === 'admin')
                    <a href="{{ route('admin.dashboard') }}">Admin</a>
                @endif
                <form action="{{ route('logout') }}" method="post">
                    @csrf
                    <button type="submit" class="link-btn">Logout</button>
                </form>
            @else
                <a href="{{ route('login') }}">Login</a>
                <a href="{{ route('register') }}">Register</a>
            @endauth
            <a class="cart-link" href="{{ route('cart.index') }}">Cart ({{ $cartCount }})</a>
        </nav>
    </div>
</header>

<main class="container">
    @if(session('success'))
        <div class="alert success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert error">{{ session('error') }}</div>
    @endif
    @if($errors->any())
        <div class="alert error">
            {{ $errors->first() }}
        </div>
    @endif
    @yield('content')
</main>

<footer class="footer">
    <p>&copy; {{ date('Y') }} {{ config('app.name', 'Almar Market') }}</p>
</footer>
</body>
</html>
