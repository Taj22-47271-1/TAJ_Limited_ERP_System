<?php

namespace Tests\Feature;

use App\Models\OnlineOrder;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderApprovalTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_approve_cod_online_order(): void
    {
        $this->seed();

        $customer = User::where('email', 'customer@tajlimited.com')->firstOrFail();
        $admin = User::where('email', 'admin@tajlimited.com')->firstOrFail();
        $product = Product::where('status', true)->where('stock', '>', 5)->where('has_sizes', false)->firstOrFail();

        // 1. Customer adds to cart
        $this->actingAs($customer)->post(route('cart.add', $product), [
            'quantity' => 2,
        ])->assertRedirect();

        // 2. Customer checks out with COD
        $response = $this->actingAs($customer)->post(route('checkout.store'), [
            'full_name' => 'Demo Customer',
            'email' => 'customer@tajlimited.com',
            'phone' => '01800000001',
            'address' => 'Uttara, Dhaka',
            'payment_method' => 'cod',
        ]);
        $response->assertRedirect();

        $order = OnlineOrder::latest()->firstOrFail();
        $this->assertSame('pending', $order->order_status);
        $this->assertSame('cod', $order->payment_method);

        // 3. Admin visits order page
        $this->actingAs($admin)->get(route('orders.show', $order))->assertOk();

        // 4. Admin approves order
        $approveResponse = $this->actingAs($admin)->post(route('orders.approve', $order));
        $approveResponse->assertSessionHasNoErrors();
        $approveResponse->assertRedirect();

        $order->refresh();
        $this->assertSame('approved', $order->order_status);
    }

    public function test_admin_can_approve_online_payment_order_with_verification(): void
    {
        $this->seed();

        $customer = User::where('email', 'customer@tajlimited.com')->firstOrFail();
        $admin = User::where('email', 'admin@tajlimited.com')->firstOrFail();
        $product = Product::where('status', true)->where('stock', '>', 5)->where('has_sizes', false)->firstOrFail();

        // 1. Customer adds to cart
        $this->actingAs($customer)->post(route('cart.add', $product), [
            'quantity' => 1,
        ])->assertRedirect();

        // 2. Customer checks out with online payment
        $response = $this->actingAs($customer)->post(route('checkout.store'), [
            'full_name' => 'Demo Customer',
            'email' => 'customer@tajlimited.com',
            'phone' => '01800000001',
            'address' => 'Uttara, Dhaka',
            'payment_method' => 'online',
        ]);
        $response->assertRedirect();

        $order = OnlineOrder::latest()->firstOrFail();
        $this->assertSame('pending', $order->order_status);
        $this->assertSame('online', $order->payment_method);
        $this->assertSame('pending', $order->payment_status);

        // 3. Admin approves order (should verify payment and commit stock without blocking)
        $approveResponse = $this->actingAs($admin)->post(route('orders.approve', $order));
        $approveResponse->assertSessionHasNoErrors();
        $approveResponse->assertRedirect();

        $order->refresh();
        $this->assertSame('approved', $order->order_status);
        $this->assertSame('paid', $order->payment_status);
    }

    public function test_admin_can_delete_product(): void
    {
        $this->seed();

        $admin = User::where('email', 'admin@tajlimited.com')->firstOrFail();
        $product = Product::create([
            'name' => 'Test Deletable Product',
            'sku' => 'TEST-DEL-01',
            'unit' => 'pcs',
            'purchase_price' => 100,
            'selling_price' => 150,
            'stock' => 10,
            'reserved_stock' => 0,
            'min_stock' => 2,
            'status' => true,
        ]);

        $this->actingAs($admin)->delete(route('products.destroy', $product))
            ->assertRedirect(route('products.index'))
            ->assertSessionHas('status');

        $this->assertSoftDeleted('products', ['id' => $product->id]);
        $this->assertNull(Product::find($product->id));
    }
}
