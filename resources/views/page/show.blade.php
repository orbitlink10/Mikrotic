@extends('layouts.app')

@php
    $articleTitle = trim((string) ($page->meta_title ?: $page->title));
    $heroSummary = trim((string) $pageMetaDescription);
    $backUrl = url()->previous() !== request()->fullUrl()
        ? url()->previous()
        : route('home');
@endphp

@section('title', $articleTitle . ' | ' . config('app.name', 'Mikrotik Kenya'))
@section('meta_description', $pageMetaDescription)
@section('canonical_url', \App\Support\CanonicalUrl::route('pages.show', $page))

@section('content')
<article class="page-story">
    <section class="page-story-hero">
        <div class="page-story-hero-copy">
            <h1 class="page-story-title">{{ $page->title }}</h1>

            @if($heroSummary !== '')
                <p class="page-story-summary">{{ $heroSummary }}</p>
            @endif

            <div class="page-story-actions">
                <a class="page-story-primary" href="{{ route('home') }}">Shop Products</a>
                <a class="page-story-secondary" href="#page-article">Read Article</a>
            </div>
        </div>

        <div class="page-story-hero-media{{ $page->image_url ? '' : ' is-empty' }}">
            @if($page->image_url)
                <img src="{{ $page->image_url }}" alt="{{ $page->alt_text ?: $page->title }}">
            @else
                <div class="page-story-hero-placeholder" aria-hidden="true">
                    <span>{{ $page->title }}</span>
                </div>
            @endif
        </div>
    </section>

    <section class="page-story-article-shell" id="page-article">
        <div class="page-story-article-head">
            <div class="page-story-article-labels">
                <p class="page-story-article-kicker">{{ $page->title }}</p>
                @if($page->heading_two)
                    <p class="page-story-article-subtitle">{{ $page->heading_two }}</p>
                @endif
            </div>

            <a class="page-story-back" href="{{ $backUrl }}">Back</a>
        </div>

        <h2 class="page-story-article-title">{{ $articleTitle }}</h2>

        @if($page->image_url)
            <figure class="page-story-feature-image">
                <img src="{{ $page->image_url }}" alt="{{ $page->alt_text ?: $page->title }}">
            </figure>
        @endif

        <div class="page-story-article-copy rich-content">{!! $pageBody !!}</div>
    </section>
</article>
@endsection
