@extends('admin.layout')

@push('head')
    <script src="{{ asset('assets/product-editor.js') }}" defer></script>
@endpush

@php
    $whyChooseItems = old('why_choose_items', $homepageContent->whyChooseItems());
    $faqItems = old('faq_items', $homepageContent->faqItems());
@endphp

@section('content')
<div class="admin-shell">
    @include('admin.partials.sidebar', ['activeAdminNav' => 'pages-content'])

    <div class="admin-main admin-management-main">
        <section class="admin-page-head admin-page-head--settings">
            <div>
                <h1 class="admin-page-title admin-page-title--settings">Update Homepage Content</h1>
            </div>

            <div class="admin-breadcrumb admin-breadcrumb--settings">
                <a href="{{ route('admin.dashboard') }}">Home</a>
                <span>/</span>
                <span>Update Content</span>
            </div>
        </section>

        <section class="panel admin-settings-panel">
            <div class="admin-settings-panel-bar">Homepage Content Management</div>

            @unless($homepageContentStorageReady)
                <div class="alert error">
                    Homepage content storage is not ready yet. Run <code>php artisan migrate</code> to create the <code>homepage_contents</code> table before saving changes.
                </div>
            @endunless

            <form class="admin-settings-form" method="post" action="{{ route('admin.pages-content.update') }}" enctype="multipart/form-data">
                @csrf

                <section class="admin-settings-group">
                    <div class="admin-settings-group-head">
                        <h2 class="admin-settings-group-title">Hero Section</h2>
                        <p class="admin-settings-help">Main heading and image shown at the top of the homepage.</p>
                    </div>

                    <div class="admin-settings-field">
                        <label class="admin-settings-label" for="hero_title">Hero Header Title</label>
                        <input
                            class="admin-settings-input"
                            id="hero_title"
                            type="text"
                            name="hero_title"
                            value="{{ old('hero_title', $homepageContent->hero_title) }}"
                            @disabled(! $homepageContentStorageReady)
                            required
                        >
                    </div>

                    <div class="admin-settings-field">
                        <label class="admin-settings-label" for="hero_description">Hero Header Description</label>
                        <textarea
                            class="admin-settings-textarea"
                            id="hero_description"
                            name="hero_description"
                            rows="3"
                            @disabled(! $homepageContentStorageReady)
                            required
                        >{{ old('hero_description', $homepageContent->hero_description) }}</textarea>
                    </div>

                    <div class="admin-settings-field">
                        <label class="admin-settings-label" for="hero_image">Hero Image (1280 x 720)</label>
                        <input
                            class="admin-settings-file"
                            id="hero_image"
                            type="file"
                            name="hero_image"
                            accept=".jpg,.jpeg,.png,.webp,image/*"
                            @disabled(! $homepageContentStorageReady)
                        >

                        @if($homepageContent->heroImageUrl())
                            <div class="admin-settings-preview">
                                <p class="admin-settings-help">Current hero image</p>
                                <img src="{{ $homepageContent->heroImageUrl() }}" alt="Current homepage hero image">
                            </div>
                        @endif
                    </div>
                </section>

                <section class="admin-settings-group">
                    <div class="admin-settings-group-head">
                        <h2 class="admin-settings-group-title">Why Choose Section</h2>
                        <p class="admin-settings-help">Cards that appear below the products on the main homepage.</p>
                    </div>

                    <div class="admin-settings-field">
                        <label class="admin-settings-label" for="why_choose_title">Why Choose Title</label>
                        <input
                            class="admin-settings-input"
                            id="why_choose_title"
                            type="text"
                            name="why_choose_title"
                            value="{{ old('why_choose_title', $homepageContent->whyChooseTitle()) }}"
                            @disabled(! $homepageContentStorageReady)
                        >
                    </div>

                    <div class="admin-settings-field">
                        <label class="admin-settings-label" for="why_choose_intro">Why Choose Intro</label>
                        <textarea
                            class="admin-settings-textarea"
                            id="why_choose_intro"
                            name="why_choose_intro"
                            rows="3"
                            @disabled(! $homepageContentStorageReady)
                        >{{ old('why_choose_intro', $homepageContent->whyChooseIntro()) }}</textarea>
                    </div>

                    <div class="admin-settings-card-grid">
                        @foreach($whyChooseItems as $index => $item)
                            <div class="admin-settings-repeater-card">
                                <h3 class="admin-settings-repeater-title">Benefit {{ $index + 1 }}</h3>

                                <div class="admin-settings-field">
                                    <label class="admin-settings-label" for="why_choose_items_{{ $index }}_title">Card Title</label>
                                    <input
                                        class="admin-settings-input"
                                        id="why_choose_items_{{ $index }}_title"
                                        type="text"
                                        name="why_choose_items[{{ $index }}][title]"
                                        value="{{ $item['title'] ?? '' }}"
                                        @disabled(! $homepageContentStorageReady)
                                    >
                                </div>

                                <div class="admin-settings-field">
                                    <label class="admin-settings-label" for="why_choose_items_{{ $index }}_description">Card Description</label>
                                    <textarea
                                        class="admin-settings-textarea"
                                        id="why_choose_items_{{ $index }}_description"
                                        name="why_choose_items[{{ $index }}][description]"
                                        rows="3"
                                        @disabled(! $homepageContentStorageReady)
                                    >{{ $item['description'] ?? '' }}</textarea>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>

                <section class="admin-settings-group">
                    <div class="admin-settings-group-head">
                        <h2 class="admin-settings-group-title">FAQ Section</h2>
                        <p class="admin-settings-help">Frequently asked questions that appear below the testimonial section.</p>
                    </div>

                    <div class="admin-settings-subgrid">
                        <div class="admin-settings-field">
                            <label class="admin-settings-label" for="faq_badge">FAQ Badge</label>
                            <input
                                class="admin-settings-input"
                                id="faq_badge"
                                type="text"
                                name="faq_badge"
                                value="{{ old('faq_badge', $homepageContent->faqBadge()) }}"
                                @disabled(! $homepageContentStorageReady)
                            >
                        </div>

                        <div class="admin-settings-field">
                            <label class="admin-settings-label" for="faq_title">FAQ Title</label>
                            <input
                                class="admin-settings-input"
                                id="faq_title"
                                type="text"
                                name="faq_title"
                                value="{{ old('faq_title', $homepageContent->faqTitle()) }}"
                                @disabled(! $homepageContentStorageReady)
                            >
                        </div>
                    </div>

                    <div class="admin-settings-field">
                        <label class="admin-settings-label" for="faq_intro">FAQ Intro</label>
                        <textarea
                            class="admin-settings-textarea"
                            id="faq_intro"
                            name="faq_intro"
                            rows="3"
                            @disabled(! $homepageContentStorageReady)
                        >{{ old('faq_intro', $homepageContent->faqIntro()) }}</textarea>
                    </div>

                    <div class="admin-settings-card-grid admin-settings-card-grid--two">
                        @foreach($faqItems as $index => $item)
                            <div class="admin-settings-repeater-card">
                                <h3 class="admin-settings-repeater-title">FAQ {{ $index + 1 }}</h3>

                                <div class="admin-settings-field">
                                    <label class="admin-settings-label" for="faq_items_{{ $index }}_question">Question</label>
                                    <input
                                        class="admin-settings-input"
                                        id="faq_items_{{ $index }}_question"
                                        type="text"
                                        name="faq_items[{{ $index }}][question]"
                                        value="{{ $item['question'] ?? '' }}"
                                        @disabled(! $homepageContentStorageReady)
                                    >
                                </div>

                                <div class="admin-settings-field">
                                    <label class="admin-settings-label" for="faq_items_{{ $index }}_answer">Answer</label>
                                    <textarea
                                        class="admin-settings-textarea"
                                        id="faq_items_{{ $index }}_answer"
                                        name="faq_items[{{ $index }}][answer]"
                                        rows="4"
                                        @disabled(! $homepageContentStorageReady)
                                    >{{ $item['answer'] ?? '' }}</textarea>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>

                <section class="admin-settings-group">
                    <div class="admin-settings-group-head">
                        <h2 class="admin-settings-group-title">Homepage Guide Content</h2>
                        <p class="admin-settings-help">Only the content written in this editor is shown on the homepage guide section.</p>
                    </div>

                    <div class="admin-settings-field">
                        <span class="admin-settings-label">Home Page Content</span>
                        @include('admin.partials.rich_editor', [
                            'name' => 'content_body',
                            'value' => old('content_body', $homepageContent->contentBody()),
                            'placeholder' => 'Write the homepage content here...',
                            'disabled' => ! $homepageContentStorageReady,
                        ])
                        <p class="admin-settings-help">Use headings, paragraphs, lists, links, images, and formatting tools directly in the editor.</p>
                    </div>
                </section>

                <div class="admin-settings-actions">
                    <button type="submit" class="admin-primary-pill" @disabled(! $homepageContentStorageReady)>Update Homepage</button>
                </div>
            </form>
        </section>
    </div>
</div>
@endsection
