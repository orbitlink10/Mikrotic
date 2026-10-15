<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminOrderManagementTest extends TestCase
{
    use RefreshDatabase;

    private function order(): Order
    {
        return Order::create([
            'user_id' => User::factory()->create()->id,
            'order_number' => 'ORD-1001',
            'status' => 'pending',
            'total_amount' => 1000,
            'shipping_name' => 'Customer',
            'shipping_email' => 'customer@example.com',
            'shipping_phone' => '0700000000',
            'shipping_address' => 'Nairobi',
            'payment_method' => 'cash_on_delivery',
        ]);
    }

    public function test_admin_can_open_order_from_list_and_save_changes(): void
    {
        $order = $this->order();
        $this->actingAs(User::factory()->create(['role' => 'admin']));

        $this->get(route('admin.orders.index'))->assertOk()
            ->assertSee(route('admin.orders.show', $order));
        $this->get(route('admin.orders.show', $order))->assertOk()
            ->assertSee('ORD-1001')->assertSee('1,000.00 KES')->assertSee('Edit Order');

        $data = [
            'status' => 'shipped',
            'shipping_name' => 'Updated Customer',
            'shipping_email' => 'updated@example.com',
            'shipping_phone' => '0711111111',
            'shipping_address' => 'Mombasa',
            'payment_method' => 'mpesa',
        ];
        $this->put(route('admin.orders.update', $order), $data + ['total_amount' => 1])
            ->assertRedirect(route('admin.orders.show', $order))->assertSessionHas('success');
        $this->assertDatabaseHas('orders', $data + ['id' => $order->id, 'total_amount' => 1000]);
    }

    public function test_invalid_changes_are_rejected(): void
    {
        $order = $this->order();
        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->from(route('admin.orders.show', $order))
            ->put(route('admin.orders.update', $order), [
                'status' => 'invalid', 'shipping_email' => 'invalid',
            ])->assertSessionHasErrors(['status', 'shipping_email', 'shipping_name']);
        $this->assertSame('pending', $order->fresh()->status);
    }

    public function test_non_admin_cannot_view_or_update_orders(): void
    {
        $order = $this->order();
        $this->get(route('admin.orders.show', $order))->assertRedirect(route('login'));
        $this->put(route('admin.orders.update', $order), [])->assertRedirect(route('login'));
        $this->actingAs($order->user);
        $this->get(route('admin.orders.show', $order))->assertForbidden();
        $this->put(route('admin.orders.update', $order), ['status' => 'paid'])->assertForbidden();
    }

    public function test_unknown_order_returns_not_found(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        $this->get('/admin/orders/999')->assertNotFound();
        $this->put('/admin/orders/999', [])->assertNotFound();
    }
}
