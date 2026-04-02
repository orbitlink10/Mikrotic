@extends('layouts.app')

@section('content')
<section class="panel">
    <h1>Add Product</h1>
    <form class="form-grid" method="post" action="{{ route('vendor.products.store') }}">
        @csrf
        <label>
            Product Name
            <input type="text" name="name" value="{{ old('name') }}" required>
        </label>
        <label>
            Category
            <select name="category_id" required>
                <option value="">Select category</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" @selected(old('category_id') == $category->id)>{{ $category->name }}</option>
                @endforeach
            </select>
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
            Description
            <textarea name="description">{{ old('description') }}</textarea>
        </label>
        <button type="submit">Save Product</button>
    </form>
</section>
@endsection
