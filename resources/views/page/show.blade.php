@extends('layouts.app')

@section('title', ($page->meta_title ?: $page->title) . ' | ' . config('app.name', 'Almar Market'))
@section('meta_description', $pageMetaDescription)

@section('content')
<article class="content-page">
    @if($page->image_url)
        <div class="content-page-media">
            <img src="{{ $page->image_url }}" alt="{{ $page->alt_text ?: $page->title }}">
        </div>
    @endif

    <div class="content-page-panel">
        <p class="content-page-type">{{ ucfirst($page->type) }}</p>
        <h1>{{ $page->title }}</h1>

        @if($page->heading_two)
            <h2>{{ $page->heading_two }}</h2>
        @endif

        <div class="content-page-copy rich-content">{!! $pageBody !!}</div>
    </div>
</article>
@endsection
