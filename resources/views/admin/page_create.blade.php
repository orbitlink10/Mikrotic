@extends('admin.layout')

@push('head')
    <script src="{{ asset('assets/product-editor.js') }}" defer></script>
@endpush

@section('content')
<div class="admin-shell">
    @include('admin.partials.sidebar', ['activeAdminNav' => 'pages'])

    <div class="admin-main admin-management-main">
        <section class="admin-page-head admin-page-head--product-create">
            <div>
                <h1 class="admin-page-title">Manage Pages</h1>
                <p class="admin-page-copy">Fill in the page details below to publish new content</p>
            </div>
        </section>

        <section class="panel admin-product-create-panel">
            @unless($pagesStorageReady)
                <div class="alert error">
                    Page storage is not ready yet. Run <code>php artisan migrate</code> to create the <code>pages</code> table before saving pages.
                </div>
            @endunless

            <form class="admin-product-create-form" method="post" action="{{ route('admin.pages.store') }}">
                @csrf

                <div class="admin-product-field">
                    <label class="admin-product-label" for="meta_title">Meta Title</label>
                    <input
                        class="admin-product-input"
                        id="meta_title"
                        type="text"
                        name="meta_title"
                        value="{{ old('meta_title') }}"
                        placeholder="Enter Meta Title"
                        @disabled(! $pagesStorageReady)
                        required
                    >
                </div>

                <div class="admin-product-field">
                    <label class="admin-product-label" for="title">Page Title</label>
                    <input
                        class="admin-product-input"
                        id="title"
                        type="text"
                        name="title"
                        value="{{ old('title') }}"
                        placeholder="Enter Keyword Title"
                        @disabled(! $pagesStorageReady)
                        required
                    >
                </div>

                <div class="admin-product-field">
                    <label class="admin-product-label" for="heading_two">Heading 2</label>
                    <input
                        class="admin-product-input"
                        id="heading_two"
                        type="text"
                        name="heading_two"
                        value="{{ old('heading_two') }}"
                        placeholder="Enter Heading 2"
                        @disabled(! $pagesStorageReady)
                        required
                    >
                </div>

                <div class="admin-product-field">
                    <label class="admin-product-label" for="type">Type</label>
                    <select class="admin-product-input admin-product-select" id="type" name="type" @disabled(! $pagesStorageReady) required>
                        <option value="post" @selected(old('type', 'post') === 'post')>Post</option>
                        <option value="page" @selected(old('type') === 'page')>Page</option>
                    </select>
                </div>

                <div class="admin-product-field">
                    <label class="admin-product-label" for="meta_description">Meta Description</label>
                    <textarea
                        class="admin-product-input admin-product-textarea"
                        id="meta_description"
                        name="meta_description"
                        rows="4"
                        placeholder="Write a short search-friendly summary"
                        @disabled(! $pagesStorageReady)
                        required
                    >{{ old('meta_description') }}</textarea>
                </div>

                <div class="admin-product-field">
                    <span class="admin-product-label">Page Description</span>

                    <div class="admin-product-editor-shell admin-post-editor-shell" data-rich-editor>
                        <div class="admin-product-editor-menubar">
                            <button type="button" class="admin-product-editor-menu-button">File</button>
                            <button type="button" class="admin-product-editor-menu-button">Edit</button>
                            <button type="button" class="admin-product-editor-menu-button">View</button>
                            <button type="button" class="admin-product-editor-menu-button">Insert</button>
                            <button type="button" class="admin-product-editor-menu-button">Format</button>
                            <button type="button" class="admin-product-editor-menu-button">Tools</button>
                            <button type="button" class="admin-product-editor-menu-button">Table</button>
                        </div>

                        <div class="admin-product-editor-toolbar editor-toolbar">
                            <button type="button" class="admin-product-editor-icon" data-command="undo" aria-label="Undo">
                                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M9 14 4 9l5-5"></path><path d="M20 20a8 8 0 0 0-8-8H4"></path></svg>
                            </button>
                            <button type="button" class="admin-product-editor-icon" data-command="redo" aria-label="Redo">
                                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m15 14 5-5-5-5"></path><path d="M4 20a8 8 0 0 1 8-8h8"></path></svg>
                            </button>
                            <button type="button" class="admin-product-editor-icon admin-product-editor-icon--text" data-command="bold" aria-label="Bold">B</button>
                            <button type="button" class="admin-product-editor-icon admin-product-editor-icon--text admin-product-editor-icon--italic" data-command="italic" aria-label="Italic">I</button>
                            <button type="button" class="admin-product-editor-icon" data-command="justifyLeft" aria-label="Align left">
                                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 6h14"></path><path d="M4 10h10"></path><path d="M4 14h14"></path><path d="M4 18h10"></path></svg>
                            </button>
                            <button type="button" class="admin-product-editor-icon" data-command="justifyCenter" aria-label="Align center">
                                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 6h14"></path><path d="M7 10h10"></path><path d="M5 14h14"></path><path d="M7 18h10"></path></svg>
                            </button>
                            <button type="button" class="admin-product-editor-icon" data-command="justifyRight" aria-label="Align right">
                                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 6h14"></path><path d="M10 10h10"></path><path d="M6 14h14"></path><path d="M10 18h10"></path></svg>
                            </button>
                            <button type="button" class="admin-product-editor-icon" data-command="outdent" aria-label="Outdent">
                                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M10 8H20"></path><path d="M10 12h10"></path><path d="M10 16H20"></path><path d="m4 12 4-4v8l-4-4Z"></path></svg>
                            </button>
                            <button type="button" class="admin-product-editor-icon" data-command="indent" aria-label="Indent">
                                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 8h10"></path><path d="M4 12h10"></path><path d="M4 16h10"></path><path d="m20 12-4 4V8l4 4Z"></path></svg>
                            </button>
                            <button type="button" class="admin-product-editor-icon" data-action="link" aria-label="Insert link">
                                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M10 13a5 5 0 0 1 0-7l1.5-1.5a5 5 0 0 1 7 7L17 13"></path><path d="M14 11a5 5 0 0 1 0 7l-1.5 1.5a5 5 0 0 1-7-7L7 11"></path></svg>
                            </button>
                            <button type="button" class="admin-product-editor-icon" data-action="image" aria-label="Insert image">
                                <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="4" y="5" width="16" height="14" rx="2"></rect><path d="m8 15 3-3 3 3 2-2 4 4"></path><path d="M9 10h.01"></path></svg>
                            </button>
                            <button type="button" class="admin-product-editor-icon" data-action="media" aria-label="Insert media">
                                <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="4" y="5" width="16" height="14" rx="2"></rect><path d="m10 9 5 3-5 3V9Z"></path></svg>
                            </button>
                            <button type="button" class="admin-product-editor-icon" data-action="code" aria-label="Insert code">
                                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m9 8-4 4 4 4"></path><path d="m15 8 4 4-4 4"></path></svg>
                            </button>
                            <button type="button" class="admin-product-editor-icon" data-action="fullscreen" aria-label="Fullscreen">
                                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M8 4H4v4"></path><path d="M16 4h4v4"></path><path d="M20 16v4h-4"></path><path d="M4 16v4h4"></path><path d="m9 9-5-5"></path><path d="m15 9 5-5"></path><path d="m15 15 5 5"></path><path d="m9 15-5 5"></path></svg>
                            </button>
                        </div>

                        <div
                            class="admin-product-editor-surface editor-surface"
                            data-editor-surface
                            data-placeholder="Write the page description here..."
                            contenteditable="{{ $pagesStorageReady ? 'true' : 'false' }}"
                        ></div>

                        <textarea class="rich-editor-input" name="body" hidden @disabled(! $pagesStorageReady)>{{ old('body') }}</textarea>
                    </div>
                </div>

                <details class="admin-product-optional-panel">
                    <summary>Optional Slug and Image</summary>
                    <div class="admin-product-optional-body">
                        <label class="admin-product-label" for="slug">Custom Slug</label>
                        <input
                            class="admin-product-input"
                            id="slug"
                            type="text"
                            name="slug"
                            value="{{ old('slug') }}"
                            placeholder="leave blank to generate automatically"
                            @disabled(! $pagesStorageReady)
                        >

                        <label class="admin-product-label" for="image_url">Image URL</label>
                        <input
                            class="admin-product-input"
                            id="image_url"
                            type="url"
                            name="image_url"
                            value="{{ old('image_url') }}"
                            placeholder="Enter image URL"
                            @disabled(! $pagesStorageReady)
                        >

                        <label class="admin-product-label" for="alt_text">Image Alt Text</label>
                        <input
                            class="admin-product-input"
                            id="alt_text"
                            type="text"
                            name="alt_text"
                            value="{{ old('alt_text') }}"
                            placeholder="Describe the image for accessibility"
                            @disabled(! $pagesStorageReady)
                        >
                        <p class="admin-product-optional-copy">Alt text is only required when you add an image.</p>
                    </div>
                </details>

                <div class="admin-product-actions">
                    <p>Choose <strong>Post</strong> for blog-style content or <strong>Page</strong> for evergreen site content.</p>
                    <button type="submit" class="admin-primary-pill" @disabled(! $pagesStorageReady)>Save Page</button>
                </div>
            </form>
        </section>
    </div>
</div>
@endsection
