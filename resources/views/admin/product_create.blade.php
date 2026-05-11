@extends('admin.layout')

@section('content')
<div class="admin-shell">
    @include('admin.partials.sidebar', ['activeAdminNav' => 'products'])

    <div class="admin-main admin-management-main">
        <section class="admin-page-head">
            <div>
                <h1 class="admin-page-title">Post Product</h1>
                <p class="admin-page-copy">Create and publish a new product from the admin store.</p>
            </div>
            <a class="admin-secondary-pill" href="{{ route('admin.products.index') }}">Back to Products</a>
        </section>

        <section class="panel admin-form-panel admin-list-panel">
            <form class="form-grid admin-form-card" method="post" action="{{ route('admin.products.store') }}">
                @csrf
                <label>
                    Product Name
                    <input type="text" name="name" value="{{ old('name') }}" required>
                </label>
                <label>
                    Existing Category
                    <select name="category_id">
                        <option value="">Select category</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" @selected(old('category_id') == $category->id)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </label>
                <label>
                    New Category
                    <input
                        type="text"
                        name="category_name"
                        value="{{ old('category_name') }}"
                        placeholder="{{ $categories->isEmpty() ? 'Create the first category here' : 'Leave blank to use selected category' }}"
                    >
                </label>
                <label>
                    Price
                    <input type="number" name="price" min="0.01" step="0.01" value="{{ old('price') }}" required>
                </label>
                <label>
                    Stock
                    <input type="number" name="stock" min="0" value="{{ old('stock', 0) }}" required>
                </label>
                <label>
                    Image URL
                    <input type="url" name="image_url" value="{{ old('image_url') }}">
                </label>
                <label>
                    Meta Description
                    <textarea name="meta_description" rows="3">{{ old('meta_description') }}</textarea>
                </label>
                <label class="admin-form-full">
                    Description
                    <textarea name="description" rows="10">{{ old('description') }}</textarea>
                </label>
                <button type="submit">Publish Product</button>
            </form>
        </section>
    </div>
</div>
@endsection
