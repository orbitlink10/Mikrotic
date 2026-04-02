@extends('layouts.app')

@section('content')
<section class="panel">
    <div class="dashboard-head">
        <div>
            <h1>Admin Dashboard</h1>
            <p class="muted">Post marketplace-managed products and handle approvals from one place.</p>
        </div>
        <div class="dashboard-actions">
            <a class="button-link" href="#admin-product-form">Post Product</a>
            <a class="button-link secondary" href="{{ route('admin.vendors.pending') }}">Manage Vendor Approvals</a>
        </div>
    </div>

    <div class="admin-stats-grid">
        <article class="admin-stat-card">
            <p class="stat-label">Users</p>
            <h3>{{ number_format($stats['total_users']) }}</h3>
        </article>
        <article class="admin-stat-card">
            <p class="stat-label">Vendors</p>
            <h3>{{ number_format($stats['total_vendors']) }}</h3>
            <small>{{ number_format($stats['pending_vendors']) }} pending</small>
        </article>
        <article class="admin-stat-card">
            <p class="stat-label">Products</p>
            <h3>{{ number_format($stats['total_products']) }}</h3>
            <small>{{ number_format($stats['active_products']) }} active</small>
        </article>
        <article class="admin-stat-card">
            <p class="stat-label">Orders</p>
            <h3>{{ number_format($stats['total_orders']) }}</h3>
            <small>{{ number_format($stats['pending_orders']) }} pending</small>
        </article>
        <article class="admin-stat-card">
            <p class="stat-label">Gross Revenue</p>
            <h3>KSh {{ number_format($stats['gross_revenue'], 2) }}</h3>
        </article>
    </div>
</section>

<section class="panel admin-workspace">
    <div>
        <h2 id="admin-product-form">Post Product</h2>
        <div class="status-card">
            <p><strong>Publishing as:</strong> {{ $adminVendor?->shop_name ?? config('app.name', 'Almar Market') . ' Official Store' }}</p>
            <p class="muted">The first product you post will create this approved admin store automatically.</p>
        </div>

        <form class="form-grid" method="post" action="{{ route('admin.products.store') }}">
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
            <button type="submit">Publish Product</button>
        </form>
    </div>

    <div>
        <h2>Admin Catalog</h2>
        @if($adminProducts->isEmpty())
            <p class="empty">No admin-posted products yet.</p>
        @else
            <div class="table-wrap">
                <table>
                    <thead>
                    <tr>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th>Status</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($adminProducts as $product)
                        <tr>
                            <td>{{ $product->name }}</td>
                            <td>{{ $product->category->name ?? 'General' }}</td>
                            <td>KSh {{ number_format((float) $product->price, 2) }}</td>
                            <td>{{ $product->stock }}</td>
                            <td>{{ ucfirst($product->status) }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</section>

<section class="panel admin-panels">
    <div>
        <h2>Recent Orders</h2>
        @if($recentOrders->isEmpty())
            <p class="empty">No orders yet.</p>
        @else
            <div class="table-wrap">
                <table>
                    <thead>
                    <tr>
                        <th>Order No</th>
                        <th>Customer</th>
                        <th>Status</th>
                        <th>Total</th>
                        <th>Date</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($recentOrders as $order)
                        <tr>
                            <td>{{ $order->order_number }}</td>
                            <td>{{ $order->user->name ?? 'Unknown' }}</td>
                            <td>{{ ucfirst($order->status) }}</td>
                            <td>KSh {{ number_format((float) $order->total_amount, 2) }}</td>
                            <td>{{ $order->created_at }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <div>
        <h2>Pending Vendors</h2>
        @if($pendingVendors->isEmpty())
            <p class="empty">No pending vendors.</p>
        @else
            <div class="table-wrap">
                <table>
                    <thead>
                    <tr>
                        <th>Shop</th>
                        <th>Owner</th>
                        <th>Email</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($pendingVendors as $vendor)
                        <tr>
                            <td>{{ $vendor->shop_name }}</td>
                            <td>{{ $vendor->user->name }}</td>
                            <td>{{ $vendor->user->email }}</td>
                            <td>
                                <form method="post" action="{{ route('admin.vendors.approve', $vendor) }}">
                                    @csrf
                                    <button type="submit">Approve</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</section>
@endsection
