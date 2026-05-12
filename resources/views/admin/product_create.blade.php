@extends('admin.layout')

@push('head')
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
    <script src="{{ asset('assets/product-editor.js') }}" defer></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const categorySelect = document.querySelector('[data-product-category]');
            const subcategorySelect = document.querySelector('[data-product-subcategory]');

            if (!(categorySelect instanceof HTMLSelectElement) || !(subcategorySelect instanceof HTMLSelectElement)) {
                return;
            }

            const subcategoriesByParent = @json(
                $categories->mapWithKeys(fn ($category) => [
                    $category->id => $category->children
                        ->map(fn ($subcategory) => ['id' => $subcategory->id, 'name' => $subcategory->name])
                        ->values()
                        ->all(),
                ])
            );
            const oldSubcategoryId = @json(old('subcategory_id'));

            const fillSubcategories = (selectedParentId) => {
                const options = subcategoriesByParent[selectedParentId] ?? [];
                const currentValue = oldSubcategoryId ?? subcategorySelect.dataset.currentValue ?? '';

                subcategorySelect.innerHTML = '';

                const placeholder = document.createElement('option');
                placeholder.value = '';
                placeholder.textContent = options.length > 0 ? 'Select Subcategory' : 'No subcategories available';
                subcategorySelect.appendChild(placeholder);

                options.forEach((subcategory) => {
                    const option = document.createElement('option');
                    option.value = String(subcategory.id);
                    option.textContent = subcategory.name;
                    option.selected = String(currentValue) === String(subcategory.id);
                    subcategorySelect.appendChild(option);
                });

                subcategorySelect.disabled = options.length === 0;
                if (options.length === 0) {
                    subcategorySelect.value = '';
                }
            };

            fillSubcategories(categorySelect.value);
            categorySelect.addEventListener('change', () => {
                delete subcategorySelect.dataset.currentValue;
                fillSubcategories(categorySelect.value);
            });
        });
    </script>
@endpush

@section('content')
@php
    $selectedCategoryId = old('category_id');
    $initialSubcategories = $categories->firstWhere('id', (int) $selectedCategoryId)?->children ?? collect();
@endphp
<div class="admin-shell">
    @include('admin.partials.sidebar', ['activeAdminNav' => 'products'])

    <div class="admin-main admin-management-main">
        <section class="space-y-6">
            <div class="relative overflow-hidden rounded-[1.75rem] border border-slate-200/80 bg-linear-to-br from-white via-slate-50 to-blue-50/70 p-6 shadow-[0_18px_50px_rgba(15,23,42,0.07)] sm:p-8">
                <div class="absolute inset-y-0 right-0 hidden w-56 bg-linear-to-bl from-blue-100/50 via-cyan-100/20 to-transparent lg:block"></div>
                <div class="relative max-w-3xl space-y-2">
                    <span class="inline-flex items-center rounded-full border border-blue-200 bg-blue-50 px-3.5 py-1 text-[11px] font-semibold tracking-[0.22em] text-blue-700 uppercase">
                        Product Builder
                    </span>
                    <div class="space-y-2">
                        <h1 class="text-3xl font-black tracking-tight text-slate-950 sm:text-4xl">Add Product</h1>
                        <p class="max-w-2xl text-base leading-7 text-slate-600 sm:text-lg">
                            Fill in the product details below to add a new item.
                        </p>
                    </div>
                </div>
            </div>

            <section class="overflow-hidden rounded-[1.75rem] border border-slate-200/80 bg-white shadow-[0_18px_50px_rgba(15,23,42,0.07)]">
                <div class="border-b border-slate-200/80 bg-slate-950 px-6 py-5 text-white sm:px-8">
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h2 class="text-xl font-bold tracking-tight">Product Details</h2>
                            <p class="mt-1 text-sm text-slate-300">Use a clean product title, accurate pricing, and a strong description.</p>
                        </div>
                        <a
                            href="{{ route('admin.products.index') }}"
                            class="inline-flex items-center justify-center rounded-full border border-white/20 px-4 py-2 text-sm font-semibold text-white transition hover:border-white/40 hover:bg-white/10"
                        >
                            Back to Products
                        </a>
                    </div>
                </div>

                <form class="space-y-6 px-6 py-6 sm:px-8 sm:py-8" method="post" action="{{ route('admin.products.store') }}">
                    @csrf

                    <div class="space-y-2">
                        <label class="block text-[11px] font-bold tracking-[0.18em] text-slate-500 uppercase" for="name">Product Name</label>
                        <input
                            class="block w-full rounded-[1rem] border border-slate-200 bg-white px-4 py-3 text-base font-medium text-slate-800 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                            id="name"
                            type="text"
                            name="name"
                            value="{{ old('name') }}"
                            placeholder="Enter product name"
                            required
                        >
                    </div>

                    <div class="grid gap-6 xl:grid-cols-2">
                        <div class="space-y-2">
                            <label class="block text-[11px] font-bold tracking-[0.18em] text-slate-500 uppercase" for="price">Price (KES)</label>
                            <input
                                class="block w-full rounded-[1rem] border border-slate-200 bg-white px-4 py-3 text-base font-medium text-slate-800 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                                id="price"
                                type="number"
                                name="price"
                                min="0.01"
                                step="0.01"
                                value="{{ old('price') }}"
                                placeholder="Enter product price"
                                required
                            >
                        </div>

                        <div class="space-y-2">
                            <label class="block text-[11px] font-bold tracking-[0.18em] text-slate-500 uppercase" for="compare_at_price">Marked Price (KES)</label>
                            <input
                                class="block w-full rounded-[1rem] border border-slate-200 bg-white px-4 py-3 text-base font-medium text-slate-800 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                                id="compare_at_price"
                                type="number"
                                name="compare_at_price"
                                min="0.01"
                                step="0.01"
                                value="{{ old('compare_at_price') }}"
                                placeholder="Enter marked price"
                            >
                        </div>
                    </div>

                    <div class="grid gap-6 xl:grid-cols-2">
                        <div class="space-y-2">
                            <label class="block text-[11px] font-bold tracking-[0.18em] text-slate-500 uppercase" for="stock">Quantity</label>
                            <input
                                class="block w-full rounded-[1rem] border border-slate-200 bg-white px-4 py-3 text-base font-medium text-slate-800 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                                id="stock"
                                type="number"
                                name="stock"
                                min="0"
                                step="1"
                                value="{{ old('stock', 0) }}"
                                placeholder="Enter product quantity"
                                required
                            >
                        </div>

                        <div class="space-y-2">
                            <label class="block text-[11px] font-bold tracking-[0.18em] text-slate-500 uppercase" for="image_url">Product Image URL</label>
                            <input
                                class="block w-full rounded-[1rem] border border-slate-200 bg-white px-4 py-3 text-base font-medium text-slate-800 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                                id="image_url"
                                type="url"
                                name="image_url"
                                value="{{ old('image_url') }}"
                                placeholder="Enter image URL"
                            >
                        </div>
                    </div>

                    <div class="grid gap-6 xl:grid-cols-2">
                        <div class="space-y-2">
                            <label class="block text-[11px] font-bold tracking-[0.18em] text-slate-500 uppercase" for="category_id">Category</label>
                            <select
                                class="block w-full rounded-[1rem] border border-slate-200 bg-white px-4 py-3 text-base font-medium text-slate-800 shadow-sm outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                                id="category_id"
                                name="category_id"
                                data-product-category
                            >
                                <option value="">Select Category</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" @selected(old('category_id') == $category->id)>{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="space-y-2">
                            <label class="block text-[11px] font-bold tracking-[0.18em] text-slate-500 uppercase" for="subcategory_id">Subcategory</label>
                            <select
                                class="block w-full rounded-[1rem] border border-slate-200 bg-white px-4 py-3 text-base font-medium text-slate-800 shadow-sm outline-none transition disabled:cursor-not-allowed disabled:bg-slate-100 disabled:text-slate-400 focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                                id="subcategory_id"
                                name="subcategory_id"
                                data-product-subcategory
                                data-current-value="{{ old('subcategory_id') }}"
                                @disabled($initialSubcategories->isEmpty())
                            >
                                <option value="">{{ $initialSubcategories->isNotEmpty() ? 'Select Subcategory' : 'No subcategories available' }}</option>
                                @foreach($initialSubcategories as $subcategory)
                                    <option value="{{ $subcategory->id }}" @selected(old('subcategory_id') == $subcategory->id)>{{ $subcategory->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="rounded-[1.4rem] border border-dashed border-slate-300 bg-slate-50/80 p-5">
                        <div class="grid gap-6 xl:grid-cols-2">
                            <div class="space-y-2">
                                <label class="block text-[11px] font-bold tracking-[0.18em] text-slate-500 uppercase" for="category_name">New Category (Optional)</label>
                                <input
                                    class="block w-full rounded-[1rem] border border-slate-200 bg-white px-4 py-3 text-base font-medium text-slate-800 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                                    id="category_name"
                                    type="text"
                                    name="category_name"
                                    value="{{ old('category_name') }}"
                                    placeholder="{{ $categories->isEmpty() ? 'Create the first category here' : 'Leave blank to use the selected category' }}"
                                >
                            </div>

                            <div class="space-y-2">
                                <label class="block text-[11px] font-bold tracking-[0.18em] text-slate-500 uppercase" for="meta_description">Meta Description</label>
                                <textarea
                                    class="block min-h-32 w-full rounded-[1rem] border border-slate-200 bg-white px-4 py-3 text-base leading-7 text-slate-800 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                                    id="meta_description"
                                    name="meta_description"
                                    rows="4"
                                    placeholder="Write a short search-friendly summary"
                                >{{ old('meta_description') }}</textarea>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <span class="block text-[11px] font-bold tracking-[0.18em] text-slate-500 uppercase">Description</span>

                        <div data-rich-editor class="overflow-hidden rounded-[1.4rem] border border-slate-200 bg-white shadow-[0_12px_32px_rgba(15,23,42,0.06)]">
                            <div class="flex flex-wrap items-center gap-2 border-b border-slate-200 bg-slate-50 px-4 py-3 text-xs font-medium text-slate-500">
                                <button type="button" class="rounded-full px-3 py-1.5 transition hover:bg-white hover:text-slate-900">File</button>
                                <button type="button" class="rounded-full px-3 py-1.5 transition hover:bg-white hover:text-slate-900">Edit</button>
                                <button type="button" class="rounded-full px-3 py-1.5 transition hover:bg-white hover:text-slate-900">View</button>
                                <button type="button" class="rounded-full px-3 py-1.5 transition hover:bg-white hover:text-slate-900">Insert</button>
                                <button type="button" class="rounded-full px-3 py-1.5 transition hover:bg-white hover:text-slate-900">Format</button>
                                <button type="button" class="rounded-full px-3 py-1.5 transition hover:bg-white hover:text-slate-900">Tools</button>
                                <button type="button" class="rounded-full px-3 py-1.5 transition hover:bg-white hover:text-slate-900">Table</button>
                            </div>

                            <div class="editor-toolbar flex flex-wrap items-center gap-2 border-b border-slate-200 bg-white px-4 py-3">
                                <button type="button" data-command="undo" aria-label="Undo" class="inline-flex min-h-10 min-w-10 items-center justify-center rounded-xl border border-slate-200 bg-slate-50 px-3 text-sm font-semibold text-slate-700 transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700">&#8630;</button>
                                <button type="button" data-command="redo" aria-label="Redo" class="inline-flex min-h-10 min-w-10 items-center justify-center rounded-xl border border-slate-200 bg-slate-50 px-3 text-sm font-semibold text-slate-700 transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700">&#8631;</button>
                                <button type="button" data-command="bold" aria-label="Bold" class="inline-flex min-h-10 min-w-10 items-center justify-center rounded-xl border border-slate-200 bg-slate-50 px-3 text-lg font-black text-slate-700 transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700">B</button>
                                <button type="button" data-command="italic" aria-label="Italic" class="inline-flex min-h-10 min-w-10 items-center justify-center rounded-xl border border-slate-200 bg-slate-50 px-3 text-lg italic text-slate-700 transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700">I</button>
                                <button type="button" data-command="justifyLeft" aria-label="Align left" class="inline-flex min-h-10 items-center justify-center rounded-xl border border-slate-200 bg-slate-50 px-3 text-xs font-semibold text-slate-700 transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700">Left</button>
                                <button type="button" data-command="justifyCenter" aria-label="Align center" class="inline-flex min-h-10 items-center justify-center rounded-xl border border-slate-200 bg-slate-50 px-3 text-xs font-semibold text-slate-700 transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700">Center</button>
                                <button type="button" data-command="justifyRight" aria-label="Align right" class="inline-flex min-h-10 items-center justify-center rounded-xl border border-slate-200 bg-slate-50 px-3 text-xs font-semibold text-slate-700 transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700">Right</button>
                                <button type="button" data-command="outdent" aria-label="Outdent" class="inline-flex min-h-10 items-center justify-center rounded-xl border border-slate-200 bg-slate-50 px-3 text-xs font-semibold text-slate-700 transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700">Out</button>
                                <button type="button" data-command="indent" aria-label="Indent" class="inline-flex min-h-10 items-center justify-center rounded-xl border border-slate-200 bg-slate-50 px-3 text-xs font-semibold text-slate-700 transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700">In</button>
                                <button type="button" data-action="link" aria-label="Insert link" class="inline-flex min-h-10 items-center justify-center rounded-xl border border-slate-200 bg-slate-50 px-3 text-xs font-semibold text-slate-700 transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700">Link</button>
                                <button type="button" data-action="image" aria-label="Insert image" class="inline-flex min-h-10 items-center justify-center rounded-xl border border-slate-200 bg-slate-50 px-3 text-xs font-semibold text-slate-700 transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700">Image</button>
                                <button type="button" data-action="media" aria-label="Insert media" class="inline-flex min-h-10 items-center justify-center rounded-xl border border-slate-200 bg-slate-50 px-3 text-xs font-semibold text-slate-700 transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700">Media</button>
                                <button type="button" data-action="code" aria-label="Insert code" class="inline-flex min-h-10 items-center justify-center rounded-xl border border-slate-200 bg-slate-50 px-3 text-xs font-semibold text-slate-700 transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700">Code</button>
                                <button type="button" data-action="fullscreen" aria-label="Fullscreen" class="inline-flex min-h-10 items-center justify-center rounded-xl border border-slate-200 bg-slate-50 px-3 text-xs font-semibold text-slate-700 transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700">Full</button>
                            </div>

                            <div
                                class="editor-surface min-h-[18rem] bg-white px-5 py-5 text-base leading-7 text-slate-800 outline-none"
                                data-editor-surface
                                data-placeholder="Write the product description here..."
                                contenteditable="true"
                            ></div>

                            <textarea class="rich-editor-input" name="description" hidden>{{ old('description') }}</textarea>
                        </div>
                    </div>

                    <div class="flex flex-col gap-4 border-t border-slate-200 pt-5 sm:flex-row sm:items-center sm:justify-between">
                        <p class="text-sm leading-6 text-slate-500">
                            Marked price is optional. If provided, it must be greater than or equal to the actual selling price.
                        </p>
                        <button
                            type="submit"
                            class="inline-flex items-center justify-center rounded-full bg-blue-600 px-7 py-3 text-sm font-semibold text-white shadow-lg shadow-blue-500/25 transition hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-200"
                        >
                            Save Product
                        </button>
                    </div>
                </form>
            </section>
        </section>
    </div>
</div>
@endsection
