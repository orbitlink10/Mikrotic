@extends('admin.layout')

@section('content')
<div class="admin-shell">
    @include('admin.partials.sidebar', ['activeAdminNav' => 'products'])

    <div class="admin-main admin-management-main">
        <section class="admin-page-head">
            <div>
                <h1 class="admin-page-title">Products</h1>
                <p class="admin-page-copy">Manage and view all products available in the system</p>
            </div>
            <a class="admin-primary-pill" href="{{ route('admin.products.create') }}">+ Add Product</a>
        </section>

        <section class="panel admin-list-panel">
            <div class="admin-list-panel-head">
                <h2>Product List</h2>
                <form class="admin-search-form" method="get" action="{{ route('admin.products.index') }}">
                    <input type="text" name="search" value="{{ $search }}" placeholder="Search by product name...">
                    <button type="submit" class="admin-search-button">Search</button>
                </form>
            </div>

            <div class="table-wrap">
                <table class="admin-data-table">
                    <thead>
                    <tr>
                        <th>#</th>
                        <th>Image</th>
                        <th>Name</th>
                        <th>Slug</th>
                        <th>Price (KES)</th>
                        <th>Google Merchant</th>
                        <th>Category</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($products as $product)
                        @php($primaryImage = $product->images->first())
                        <tr>
                            <td>{{ $products->firstItem() + $loop->index }}</td>
                            <td>
                                @if($primaryImage?->image_url)
                                    <img class="admin-thumb admin-thumb--product" src="{{ $primaryImage->image_url }}" alt="{{ $product->name }}">
                                @else
                                    <div class="admin-thumb admin-thumb--placeholder admin-thumb--product">No Image</div>
                                @endif
                            </td>
                            <td>{{ $product->name }}</td>
                            <td>{{ $product->slug }}</td>
                            <td>{{ number_format((float) $product->price, 2) }}</td>
                            <td>No</td>
                            <td>{{ $product->category?->name ?? 'General' }}</td>
                            <td>
                                <div class="admin-action-stack">
                                    <button type="button" class="admin-outline-action tone-primary">Update</button>
                                    <button type="button" class="admin-outline-action tone-danger">Delete</button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="admin-empty-cell">No products found.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</div>
@endsection
