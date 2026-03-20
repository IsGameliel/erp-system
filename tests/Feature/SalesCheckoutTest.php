<?php

namespace Tests\Feature;

use App\Mail\OrderReceiptMail;
use App\Models\Product;
use App\Models\SalesOrder;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class SalesCheckoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_sales_officer_can_checkout_sale_and_create_customer_inline(): void
    {
        Mail::fake();

        $store = Store::create([
            'name' => 'Main Store',
            'code' => 'MAIN',
            'location' => 'Lagos',
        ]);

        $salesOfficer = User::factory()->salesOfficer()->create([
            'store_id' => $store->id,
        ]);

        $product = Product::factory()->create([
            'stock_quantity' => 10,
            'selling_price' => 250,
            'status' => Product::STATUS_ACTIVE,
        ]);

        $response = $this
            ->actingAs($salesOfficer)
            ->post(route('sales-orders.store'), [
                'order_date' => now()->toDateString(),
                'status' => SalesOrder::STATUS_DELIVERED,
                'payment_status' => SalesOrder::PAYMENT_STATUS_PAID,
                'payment_method' => SalesOrder::PAYMENT_METHOD_CASH,
                'tax' => 0,
                'discount' => 0,
                'customer' => [
                    'full_name' => 'Walk In Customer',
                    'phone' => '08000000000',
                    'email' => 'walkin@example.com',
                ],
                'items' => [
                    [
                        'product_id' => $product->id,
                        'quantity' => 2,
                        'unit_price' => 250,
                    ],
                ],
            ]);

        $salesOrder = SalesOrder::firstOrFail();

        $response->assertRedirect(route('sales-orders.receipt', $salesOrder));
        $this->assertDatabaseHas('customers', [
            'full_name' => 'Walk In Customer',
        ]);
        $this->assertDatabaseHas('sales_orders', [
            'id' => $salesOrder->id,
            'store_id' => $store->id,
            'payment_method' => SalesOrder::PAYMENT_METHOD_CASH,
            'payment_status' => SalesOrder::PAYMENT_STATUS_PAID,
        ]);
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'stock_quantity' => 8,
        ]);
        Mail::assertSent(OrderReceiptMail::class);
    }
}
