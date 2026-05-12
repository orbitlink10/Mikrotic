@extends('admin.layout')

@push('head')
    <script src="{{ asset('assets/product-editor.js') }}" defer></script>
@endpush

@section('content')
<div class="admin-shell">
    @include('admin.partials.sidebar', ['activeAdminNav' => 'pages'])

    <div class="admin-main admin-management-main">
        <section class="admin-page-head admin-page-head--post-builder">
            <div>
                <h1 class="admin-page-title admin-page-title--post-builder">Manage Pages</h1>
            </div>
        </section>

        <section class="panel admin-post-builder-panel">
            <div class="admin-post-builder-bar">Add New Post</div>

            @unless($pagesStorageReady)
                <div class="alert error">
                    Page storage is not ready yet. Run <code>php artisan migrate</code> to create the <code>pages</code> table before saving pages.
                </div>
            @endunless

            <form class="admin-post-builder-form" method="post" action="{{ route('admin.pages.store') }}">
                @csrf

                <div class="admin-post-builder-field">
                    <label class="admin-post-builder-label" for="meta_title">Meta Title</label>
                    <input
                        class="admin-post-builder-input"
                        id="meta_title"
                        type="text"
                        name="meta_title"
                        value="{{ old('meta_title') }}"
                        placeholder="Enter Meta Title"
                        @disabled(! $pagesStorageReady)
                        required
                    >
                </div>

                <div class="admin-post-builder-field">
                    <label class="admin-post-builder-label" for="meta_description">Meta Description</label>
                    <input
                        class="admin-post-builder-input"
                        id="meta_description"
                        type="text"
                        name="meta_description"
                        value="{{ old('meta_description') }}"
                        placeholder="Enter Meta Description"
                        @disabled(! $pagesStorageReady)
                        required
                    >
                </div>

                <div class="admin-post-builder-field">
                    <label class="admin-post-builder-label" for="title">Page Title</label>
                    <input
                        class="admin-post-builder-input"
                        id="title"
                        type="text"
                        name="title"
                        value="{{ old('title') }}"
                        placeholder="Enter Keyword Title"
                        @disabled(! $pagesStorageReady)
                        required
                    >
                </div>

                <div class="admin-post-builder-field">
                    <label class="admin-post-builder-label" for="alt_text">Image Alt Text</label>
                    <input
                        class="admin-post-builder-input"
                        id="alt_text"
                        type="text"
                        name="alt_text"
                        value="{{ old('alt_text') }}"
                        placeholder="Enter Image Alt Text"
                        @disabled(! $pagesStorageReady)
                        required
                    >
                </div>

                <div class="admin-post-builder-field">
                    <label class="admin-post-builder-label" for="heading_two">Heading 2</label>
                    <input
                        class="admin-post-builder-input"
                        id="heading_two"
                        type="text"
                        name="heading_two"
                        value="{{ old('heading_two') }}"
                        placeholder="Enter Heading 2"
                        @disabled(! $pagesStorageReady)
                        required
                    >
                </div>

                <div class="admin-post-builder-field">
                    <label class="admin-post-builder-label" for="type">Type</label>
                    <select class="admin-post-builder-input admin-post-builder-select" id="type" name="type" @disabled(! $pagesStorageReady) required>
                        <option value="post" @selected(old('type', 'post') === 'post')>Post</option>
                        <option value="page" @selected(old('type') === 'page')>Page</option>
                    </select>
                </div>

                <div class="admin-post-builder-field">
                    <span class="admin-post-builder-label">Page Description:</span>

                    <div class="admin-post-editor-shell editor-shell" data-rich-editor>
                        <div class="admin-post-editor-menubar">
                            <button type="button" class="admin-post-editor-menu-button">File</button>
                            <button type="button" class="admin-post-editor-menu-button">Edit</button>
                            <button type="button" class="admin-post-editor-menu-button">View</button>
                            <button type="button" class="admin-post-editor-menu-button">Insert</button>
                            <button type="button" class="admin-post-editor-menu-button">Format</button>
                            <button type="button" class="admin-post-editor-menu-button">Tools</button>
                            <button type="button" class="admin-post-editor-menu-button">Table</button>
                        </div>

                        <div class="admin-post-editor-toolbar">
                            <button type="button" class="admin-post-editor-icon" data-command="undo" aria-label="Undo">&#8630;</button>
                            <button type="button" class="admin-post-editor-icon" data-command="redo" aria-label="Redo">&#8631;</button>
                            <button type="button" class="admin-post-editor-icon admin-post-editor-icon--bold" data-command="bold" aria-label="Bold">B</button>
                            <button type="button" class="admin-post-editor-icon admin-post-editor-icon--italic" data-command="italic" aria-label="Italic">I</button>
                            <button type="button" class="admin-post-editor-icon" data-command="justifyLeft" aria-label="Align left">Left</button>
                            <button type="button" class="admin-post-editor-icon" data-command="justifyCenter" aria-label="Align center">Center</button>
                            <button type="button" class="admin-post-editor-icon" data-command="justifyRight" aria-label="Align right">Right</button>
                            <button type="button" class="admin-post-editor-icon" data-command="outdent" aria-label="Outdent">Out</button>
                            <button type="button" class="admin-post-editor-icon" data-command="indent" aria-label="Indent">In</button>
                            <button type="button" class="admin-post-editor-icon" data-action="link" aria-label="Insert link">Link</button>
                            <button type="button" class="admin-post-editor-icon" data-action="image" aria-label="Insert image">Image</button>
                            <button type="button" class="admin-post-editor-icon" data-action="media" aria-label="Insert media">Media</button>
                            <button type="button" class="admin-post-editor-icon" data-action="code" aria-label="Insert code">Code</button>
                            <button type="button" class="admin-post-editor-icon" data-action="fullscreen" aria-label="Fullscreen">Full</button>
                        </div>

                        <div
                            class="admin-post-editor-surface editor-surface"
                            data-editor-surface
                            data-placeholder="Write the page description here..."
                            contenteditable="{{ $pagesStorageReady ? 'true' : 'false' }}"
                        ></div>

                        <textarea class="rich-editor-input" name="body" hidden @disabled(! $pagesStorageReady)>{{ old('body') }}</textarea>
                    </div>
                </div>

                <div class="admin-post-builder-actions">
                    <button type="submit" class="admin-primary-pill" @disabled(! $pagesStorageReady)>Save Post</button>
                </div>
            </form>
        </section>
    </div>
</div>
@endsection
