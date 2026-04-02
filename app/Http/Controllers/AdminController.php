<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminController extends Controller
{
    public function dashboard(): View
    {
        $stats = [
            'total_users' => User::count(),
            'total_vendors' => Vendor::count(),
            'pending_vendors' => Vendor::where('is_approved', false)->count(),
            'approved_vendors' => Vendor::where('is_approved', true)->count(),
            'total_products' => Product::count(),
            'active_products' => Product::where('status', 'active')->count(),
            'total_orders' => Order::count(),
            'pending_orders' => Order::where('status', 'pending')->count(),
            'gross_revenue' => (float) Order::sum('total_amount'),
        ];

        $recentOrders = Order::query()
            ->with('user')
            ->latest()
            ->limit(8)
            ->get();

        $pendingVendors = Vendor::query()
            ->with('user')
            ->where('is_approved', false)
            ->latest()
            ->limit(8)
            ->get();

        return view('admin.dashboard', [
            'stats' => $stats,
            'recentOrders' => $recentOrders,
            'pendingVendors' => $pendingVendors,
        ]);
    }

    public function pendingVendors(): View
    {
        return view('admin.vendors', [
            'vendors' => Vendor::query()
                ->with('user')
                ->where('is_approved', false)
                ->latest()
                ->get(),
        ]);
    }

    public function approveVendor(Request $request, Vendor $vendor): RedirectResponse
    {
        $vendor->update(['is_approved' => true]);
        $vendor->products()->where('status', 'draft')->update(['status' => 'active']);
        $vendor->user()->update(['role' => 'vendor']);

        return back()->with('success', 'Vendor approved successfully.');
    }
}
