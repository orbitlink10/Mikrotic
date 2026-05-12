@extends('admin.layout')

@push('head')
    <script src="{{ asset('assets/product-editor.js') }}" defer></script>
@endpush

@section('content')
@php
    $selectedParentId = old('parent_id', $defaultParentId);
    $showOptionalCategorySettings = filled($selectedParentId) || $errors->has('image');
@endphp
<div class="admin-shell">
    @include('admin.partials.sidebar', ['activeAdminNav' => 'categories'])

    <div class="admin-main admin-management-main">
        @unless($categoryContentFieldsReady)
            <div class="alert error">
                Category content fields are not ready yet. Run <code>php artisan migrate</code> to save meta descriptions and category descriptions.
            </div>
        @endunless

        <section class="admin-page-head admin-page-head--product-create">
            <div>
                <h1 class="admin-page-title">Create Category</h1>
            </div>
        </section>

        <section class="panel admin-product-create-panel">
            <form class="admin-product-create-form" method="post" action="{{ route('admin.categories.store') }}" enctype="multipart/form-data">
                @csrf

                <div class="admin-product-field">
                    <label class="admin-product-label" for="name">
                        Name <span class="admin-field-required">*</span>
                    </label>
                    <input
                        class="admin-product-input"
                        id="name"
                        type="text"
                        name="name"
                        value="{{ old('name') }}"
                        placeholder="Enter category name"
                        required
                    >
                </div>

                <div class="admin-product-field">
                    <label class="admin-product-label" for="meta_description">Meta description</label>
                    <textarea
                        class="admin-product-input admin-product-textarea"
                        id="meta_description"
                        name="meta_description"
                        rows="4"
                        placeholder="Enter category meta description"
                    >{{ old('meta_description') }}</textarea>
                </div>

                <div class="admin-product-field">
                    <span class="admin-product-label">Description (Optional)</span>

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
                            data-placeholder="Enter category description"
                            contenteditable="true"
                        ></div>

                        <textarea class="rich-editor-input" name="description" hidden>{{ old('description') }}</textarea>
                    </div>
                </div>

                <details class="admin-product-optional-panel" {{ $showOptionalCategorySettings ? 'open' : '' }}>
                    <summary>Parent Category and Image (Optional)</summary>
                    <div class="admin-product-optional-body">
                        <label class="admin-product-label" for="parent_id">Parent Category</label>
                        <select class="admin-product-input admin-product-select" id="parent_id" name="parent_id">
                            <option value="">Top Level Category</option>
                            @foreach($parents as $parent)
                                <option value="{{ $parent->id }}" @selected($selectedParentId == $parent->id)>{{ $parent->name }}</option>
                            @endforeach
                        </select>

                        <label class="admin-product-label" for="image">Upload Image</label>
                        <input
                            class="admin-product-file"
                            id="image"
                            type="file"
                            name="image"
                            accept=".jpg,.jpeg,.png,.webp"
                        >
                        <p class="admin-product-optional-copy">Use a parent category only when you are creating a sub category.</p>
                    </div>
                </details>

                <div class="admin-product-actions">
                    <p>Descriptions are optional, but they help when categories need search-friendly and richer content later.</p>
                    <button type="submit" class="admin-primary-pill">Save Category</button>
                </div>
            </form>
        </section>
    </div>
</div>
@endsection
