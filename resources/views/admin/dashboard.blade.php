@extends('layouts.app')

@section('content')
<section class="panel">
    <div class="dashboard-head">
        <h1>Admin Dashboard</h1>
        <a class="button-link" href="{{ route('admin.vendors.pending') }}">Manage Vendor Approvals</a>
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
