<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @php
        $homepageBrandContent = \App\Models\HomepageContent::current();
        $siteLogoUrl = $homepageBrandContent->siteLogoUrl();
        $pageTitle = trim($__env->yieldContent('title')) ?: config('app.name', 'Mikrotik Kenya');
        $pageDescription = trim($__env->yieldContent('meta_description')) ?: 'Browse MikroTik products, networking equipment and current prices in Kenya.';
        $marketCssVersion = @filemtime(public_path('assets/market.css')) ?: time();
        $canonicalUrl = trim($__env->yieldContent('canonical_url'));
        $robotsContent = trim($__env->yieldContent('robots'));
        $openGraphTitle = trim($__env->yieldContent('og_title')) ?: $pageTitle;
        $openGraphDescription = trim($__env->yieldContent('og_description')) ?: $pageDescription;
        $openGraphImage = trim($__env->yieldContent('og_image')) ?: $siteLogoUrl;
        $openGraphType = trim($__env->yieldContent('og_type')) ?: 'website';
        $organizationSchema = \App\Support\StructuredData::organization($homepageBrandContent);
        $headerPhone = $homepageBrandContent->contactPhone();
        $headerWhatsApp = $homepageBrandContent->contactWhatsApp() ?: $headerPhone;
        $headerPhoneHref = $headerPhone ? 'tel:'.preg_replace('/[^\d+]+/', '', $headerPhone) : null;
        $headerWhatsAppDigits = preg_replace('/\D+/', '', (string) $headerWhatsApp);

        if (\Illuminate\Support\Str::startsWith($headerWhatsAppDigits, '0')) {
            $headerWhatsAppDigits = '254'.ltrim($headerWhatsAppDigits, '0');
        }

        $headerWhatsAppHref = $headerWhatsAppDigits !== '' ? 'https://wa.me/'.$headerWhatsAppDigits : null;
    @endphp
    <title>{!! $pageTitle !!}</title>
    <meta name="description" content="{!! $pageDescription !!}">
    <link rel="canonical" href="{!! $canonicalUrl !== '' ? $canonicalUrl : \App\Support\CanonicalUrl::current() !!}">
    @if($robotsContent !== '')
        <meta name="robots" content="{!! $robotsContent !!}">
    @endif
    <meta property="og:type" content="{!! $openGraphType !!}">
    <meta property="og:site_name" content="{{ config('app.name', 'Mikrotik Kenya') }}">
    <meta property="og:title" content="{!! $openGraphTitle !!}">
    <meta property="og:description" content="{!! $openGraphDescription !!}">
    <meta property="og:url" content="{!! $canonicalUrl !== '' ? $canonicalUrl : \App\Support\CanonicalUrl::current() !!}">
    @if($openGraphImage)
        <meta property="og:image" content="{{ \App\Support\CanonicalUrl::absoluteAsset($openGraphImage) }}">
    @endif
    <meta name="twitter:card" content="{{ $openGraphImage ? 'summary_large_image' : 'summary' }}">
    <meta name="twitter:title" content="{!! $openGraphTitle !!}">
    <meta name="twitter:description" content="{!! $openGraphDescription !!}">
    @if($openGraphImage)
        <meta name="twitter:image" content="{{ \App\Support\CanonicalUrl::absoluteAsset($openGraphImage) }}">
    @endif
    <script type="application/ld+json">@json($organizationSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)</script>
    <link rel="stylesheet" href="{{ asset('assets/market.css') }}?v={{ $marketCssVersion }}">
    @stack('head')
</head>
<body>
<header class="top-header">
    <div class="nav-wrap">
        <a href="{{ route('home') }}" class="logo" aria-label="Go to homepage">
            @if($siteLogoUrl)
                <img class="logo-image" src="{{ $siteLogoUrl }}" alt="{{ config('app.name', 'Mikrotik Kenya') }}">
            @else
                <span class="logo-main logo-main--single">{{ config('app.name', 'Mikrotik Kenya') }}</span>
            @endif
        </a>

        <form class="search-form" method="get" action="{{ route('home') }}">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search MikroTik routers, switches and accessories">
            <button type="submit">Search</button>
        </form>

        <nav class="top-links top-contact-links" aria-label="Contact">
            @if($headerPhone && $headerPhoneHref)
                <a class="contact-link contact-link--phone" href="{{ $headerPhoneHref }}">Phone {{ $headerPhone }}</a>
            @endif
            @if($headerWhatsApp && $headerWhatsAppHref)
                <a class="contact-link contact-link--whatsapp" href="{{ $headerWhatsAppHref }}" target="_blank" rel="noopener noreferrer">WhatsApp {{ $headerWhatsApp }}</a>
            @endif
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
    <nav class="footer-links" aria-label="Footer">
        <a href="{{ route('pages.show', ['page' => 'about-us']) }}">About Us</a>
        <a href="{{ route('pages.show', ['page' => 'contact-us']) }}">Contact Us</a>
        <a href="{{ route('pages.show', ['page' => 'delivery-policy']) }}">Delivery Policy</a>
        <a href="{{ route('pages.show', ['page' => 'returns-policy']) }}">Returns Policy</a>
        <a href="{{ route('pages.show', ['page' => 'warranty-policy']) }}">Warranty Policy</a>
        <a href="{{ route('pages.show', ['page' => 'privacy-policy']) }}">Privacy Policy</a>
        <a href="{{ route('pages.show', ['page' => 'terms-and-conditions']) }}">Terms and Conditions</a>
    </nav>
    <p>&copy; {{ date('Y') }} {{ config('business.name', config('app.name', 'Mikrotik Kenya')) }}</p>
</footer>
</body>
</html>
