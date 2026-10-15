@extends('admin.layout')

@section('content')
<div class="admin-shell">
    @include('admin.partials.sidebar', ['activeAdminNav' => 'orders'])

    <div class="admin-main admin-management-main">
        <section class="admin-page-head">
            <div>
                <h1 class="admin-page-title">Order Details</h1>
                <p class="admin-page-copy">{{ $order->order_number }}</p>
            </div>
            <a class="admin-secondary-pill" href="{{ route('admin.orders.index') }}">Back to Orders</a>
        </section>

        <section class="panel admin-detail-card">
            <div class="admin-detail-grid">
                <div><p class="admin-detail-label">Customer</p><p class="admin-detail-copy">{{ $order->user?->name ?? $order->shipping_name }}</p></div>
                <div><p class="admin-detail-label">Placed At</p><p class="admin-detail-copy">{{ $order->created_at?->format('d/m/Y H:i:s') }}</p></div>
                <div><p class="admin-detail-label">Status</p><p class="admin-detail-copy">{{ ucfirst($order->status) }}</p></div>
                <div><p class="admin-detail-label">Total</p><p class="admin-detail-copy">{{ number_format((float) $order->total_amount, 2) }} KES</p></div>
            </div>
            <h2>Order Items</h2>
            @forelse($order->items as $item)
                <div class="admin-detail-grid">
                    <p>{{ $item->product?->name ?? 'Unavailable product' }}</p>
                    <p>Quantity: {{ $item->quantity }}</p>
                    <p>Unit price: {{ number_format((float) $item->unit_price, 2) }} KES</p>
                    <p>Subtotal: {{ number_format((float) $item->line_total, 2) }} KES</p>
                </div>
            @empty
                <p class="empty">No items recorded for this order.</p>
            @endforelse
        </section>

        <section class="panel admin-product-create-panel">
            <h2>Edit Order</h2>
            <form class="admin-product-create-form" method="post" action="{{ route('admin.orders.update', $order) }}">
                @csrf
                @method('PUT')
                <div class="admin-product-field">
                    <label class="admin-product-label" for="status">Status</label>
                    <select class="admin-product-input" id="status" name="status" required>
                        @foreach($statuses as $status)
                            <option value="{{ $status }}" @selected(old('status', $order->status) === $status)>{{ ucfirst($status) }}</option>
                        @endforeach
                    </select>
                    @error('status')<p class="alert error">{{ $message }}</p>@enderror
                </div>
                @foreach(['shipping_name' => 'Delivery Name', 'shipping_email' => 'Delivery Email', 'shipping_phone' => 'Delivery Phone', 'shipping_address' => 'Delivery Address', 'payment_method' => 'Payment Method'] as $field => $label)
                    <div class="admin-product-field">
                        <label class="admin-product-label" for="{{ $field }}">{{ $label }}</label>
                        <input class="admin-product-input" id="{{ $field }}" name="{{ $field }}" type="{{ $field === 'shipping_email' ? 'email' : ($field === 'shipping_phone' ? 'tel' : 'text') }}" value="{{ old($field, $order->$field) }}" maxlength="{{ in_array($field, ['shipping_phone', 'payment_method']) ? 50 : 255 }}" required>
                        @error($field)<p class="alert error">{{ $message }}</p>@enderror
                    </div>
                @endforeach
                <div class="admin-product-actions">
                    <button class="admin-primary-pill" type="submit">Save Changes</button>
                </div>
            </form>
        </section>
    </div>
</div>
@endsection
