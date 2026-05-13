@extends('layouts.app')

@php
    $catalogTitle = $currentCategory
        ? $currentCategory->name . ' | ' . config('app.name', 'Mikrotik Kenya')
        : config('app.name', 'Mikrotik Kenya');
    $catalogMetaDescription = $currentCategory?->meta_description
        ?: ($currentCategory ? \App\Support\ProductContent::excerpt($currentCategory->description, 160) : 'Browse products from our multi-vendor marketplace.');
    $showHomepageSections = $search === '' && !$currentCategory && $products->currentPage() === 1;
    $whyChooseIcons = [
        <<<'SVG'
<svg viewBox="0 0 48 48" aria-hidden="true"><path d="M24 6l7 3 7 8-2 10-7 5-5 10-5-10-7-5-2-10 7-8 7-3zM17 37h14" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
SVG,
        <<<'SVG'
<svg viewBox="0 0 48 48" aria-hidden="true"><path d="M6 15h22v14H6zm22 4h7l5 5v5h-12zM14 34a3 3 0 106 0 3 3 0 00-6 0zm17 0a3 3 0 106 0 3 3 0 00-6 0z" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
SVG,
        <<<'SVG'
<svg viewBox="0 0 48 48" aria-hidden="true"><path d="M11 12l9 9m0 0l-4 4a4 4 0 000 6l2 2a4 4 0 006 0l4-4m-8-8l10-10m-1 1l9 9m-10 10l4 4a4 4 0 010 6l-2 2a4 4 0 01-6 0l-4-4" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
SVG,
        <<<'SVG'
<svg viewBox="0 0 48 48" aria-hidden="true"><path d="M8 16h32v20H8zm0 0l6-6h20l6 6M14 26h8" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
SVG,
        <<<'SVG'
<svg viewBox="0 0 48 48" aria-hidden="true"><path d="M24 6l12 4v10c0 8-5 14-12 18C17 34 12 28 12 20V10zm-5 14l4 4 7-8" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
SVG,
        <<<'SVG'
<svg viewBox="0 0 48 48" aria-hidden="true"><path d="M15 26v-6a9 9 0 0118 0v6m-18 0v7a3 3 0 003 3h12a3 3 0 003-3v-7m-18 0h18m-9 7v3" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
SVG,
    ];
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

        @if($showHomepageSections)
            <section class="home-extra-sections">
                <section class="home-section home-section--why-choose">
                    <div class="home-section-head">
                        <h2>{{ $homepageContent->whyChooseTitle() }}</h2>
                        @if($homepageContent->whyChooseIntro())
                            <p>{{ $homepageContent->whyChooseIntro() }}</p>
                        @endif
                    </div>

                    <div class="why-choose-grid">
                        @foreach($homepageContent->whyChooseItems() as $item)
                            <article class="why-choose-card">
                                <div class="why-choose-icon">{!! $whyChooseIcons[$loop->index % count($whyChooseIcons)] !!}</div>
                                <h3>{{ $item['title'] }}</h3>
                                <p>{{ $item['description'] }}</p>
                            </article>
                        @endforeach
                    </div>
                </section>

                @if($testimonials->isNotEmpty())
                    <section class="home-section home-section--testimonials">
                        <div class="home-section-head">
                            @if($homepageContent->testimonialsBadge())
                                <p class="home-section-kicker">{{ $homepageContent->testimonialsBadge() }}</p>
                            @endif
                            <h2>{{ $homepageContent->testimonialsTitle() }}</h2>
                            @if($homepageContent->testimonialsIntro())
                                <p>{{ $homepageContent->testimonialsIntro() }}</p>
                            @endif
                        </div>

                        <div class="testimonial-grid">
                            @foreach($testimonials as $testimonial)
                                @php($rating = max(1, min(5, (int) $testimonial->rating)))
                                <article class="testimonial-card">
                                    <span class="testimonial-quote-mark" aria-hidden="true">&ldquo;</span>
                                    <div class="testimonial-stars" aria-label="{{ $rating }} out of 5 stars">{{ str_repeat('★', $rating) }}</div>
                                    <p class="testimonial-quote">{{ $testimonial->quote }}</p>
                                    <h3>{{ $testimonial->name }}</h3>
                                    <p class="testimonial-role">{{ $testimonial->role }}</p>
                                </article>
                            @endforeach
                        </div>
                    </section>
                @endif

                <section class="home-section home-section--faq">
                    <div class="home-section-head">
                        @if($homepageContent->faqBadge())
                            <p class="home-section-kicker">{{ $homepageContent->faqBadge() }}</p>
                        @endif
                        <h2>{{ $homepageContent->faqTitle() }}</h2>
                        @if($homepageContent->faqIntro())
                            <p>{{ $homepageContent->faqIntro() }}</p>
                        @endif
                    </div>

                    <div class="faq-list">
                        @foreach($homepageContent->faqItems() as $item)
                            <details class="faq-item" @if($loop->first) open @endif>
                                <summary>{{ $item['question'] }}</summary>
                                <p>{{ $item['answer'] }}</p>
                            </details>
                        @endforeach
                    </div>
                </section>

                <section class="home-section home-section--guide">
                    <div class="home-guide-shell">
                        <div class="home-guide-border" aria-hidden="true"></div>
                        <div class="home-guide-card">
                            <div class="home-section-head home-section-head--guide">
                                @if($homepageContent->contentBadge())
                                    <p class="home-section-kicker">{{ $homepageContent->contentBadge() }}</p>
                                @endif
                                <h2>{{ $homepageContent->contentTitle() }}</h2>
                                @if($homepageContent->contentIntro())
                                    <p>{{ $homepageContent->contentIntro() }}</p>
                                @endif
                            </div>

                            <div class="home-guide-body rich-content">
                                {!! $homepageContent->contentBody() !!}
                            </div>
                        </div>
                    </div>
                </section>
            </section>
        @endif
    </div>
</section>
@endsection
