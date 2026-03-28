<?php

namespace Tests\Feature;

use App\Mail\OrderReceiptMail;
use App\Models\Product;
use App\Models\Customer;
use App\Models\CustomerProductDiscount;
use App\Models\SalesOrder;
use App\Models\StoreProductQuantity;
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
            'account_balance' => 0,
        ]);
        $this->assertDatabaseHas('sales_orders', [
            'id' => $salesOrder->id,
            'store_id' => $store->id,
            'payment_method' => SalesOrder::PAYMENT_METHOD_CASH,
            'payment_status' => SalesOrder::PAYMENT_STATUS_PAID,
            'amount_paid' => 500,
        ]);
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'stock_quantity' => 8,
        ]);
        Mail::assertSent(OrderReceiptMail::class);
    }

    public function test_sales_officer_cannot_submit_manual_discount_but_saved_customer_product_discount_is_applied(): void
    {
        $store = Store::create([
            'name' => 'Main Store',
            'code' => 'MAIN',
            'location' => 'Lagos',
        ]);

        $salesOfficer = User::factory()->salesOfficer()->create([
            'store_id' => $store->id,
        ]);

        $customer = Customer::create([
            'full_name' => 'Discounted Customer',
            'status' => Customer::STATUS_ACTIVE,
            'created_by' => $salesOfficer->id,
        ]);

        $product = Product::factory()->create([
            'stock_quantity' => 10,
            'selling_price' => 250,
            'status' => Product::STATUS_ACTIVE,
        ]);

        CustomerProductDiscount::create([
            'customer_id' => $customer->id,
            'product_id' => $product->id,
            'store_id' => $store->id,
            'discount_amount' => 25,
        ]);

        $invalidResponse = $this
            ->actingAs($salesOfficer)
            ->post(route('sales-orders.store'), [
                'customer_mode' => 'existing',
                'customer_id' => $customer->id,
                'order_date' => now()->toDateString(),
                'status' => SalesOrder::STATUS_DELIVERED,
                'payment_status' => SalesOrder::PAYMENT_STATUS_PAID,
                'payment_method' => SalesOrder::PAYMENT_METHOD_CASH,
                'discount' => 50,
                'items' => [
                    [
                        'product_id' => $product->id,
                        'quantity' => 1,
                        'unit_price' => 250,
                    ],
                ],
            ]);

        $invalidResponse->assertSessionHasErrors('discount');
        $this->assertDatabaseCount('sales_orders', 0);

        $validResponse = $this
            ->actingAs($salesOfficer)
            ->post(route('sales-orders.store'), [
                'customer_mode' => 'existing',
                'customer_id' => $customer->id,
                'order_date' => now()->toDateString(),
                'status' => SalesOrder::STATUS_DELIVERED,
                'payment_status' => SalesOrder::PAYMENT_STATUS_PAID,
                'payment_method' => SalesOrder::PAYMENT_METHOD_CASH,
                'items' => [
                    [
                        'product_id' => $product->id,
                        'quantity' => 2,
                        'unit_price' => 250,
                    ],
                ],
            ]);

        $salesOrder = SalesOrder::firstOrFail();

        $validResponse->assertRedirect(route('sales-orders.receipt', $salesOrder));
        $this->assertDatabaseHas('sales_orders', [
            'id' => $salesOrder->id,
            'discount' => 50,
            'total' => 450,
            'amount_paid' => 450,
        ]);
        $this->assertDatabaseHas('customers', [
            'id' => $customer->id,
            'account_balance' => 0,
        ]);
    }

    public function test_credit_sale_reduces_customer_account_balance(): void
    {
        $store = Store::create([
            'name' => 'Main Store',
            'code' => 'MAIN',
            'location' => 'Lagos',
        ]);

        $salesOfficer = User::factory()->salesOfficer()->create([
            'store_id' => $store->id,
        ]);

        $customer = Customer::create([
            'full_name' => 'Credit Customer',
            'status' => Customer::STATUS_ACTIVE,
            'account_balance' => 0,
            'created_by' => $salesOfficer->id,
        ]);

        $product = Product::factory()->create([
            'stock_quantity' => 20,
            'selling_price' => 1500,
            'status' => Product::STATUS_ACTIVE,
        ]);

        $response = $this
            ->actingAs($salesOfficer)
            ->post(route('sales-orders.store'), [
                'customer_mode' => 'existing',
                'customer_id' => $customer->id,
                'order_date' => now()->toDateString(),
                'status' => SalesOrder::STATUS_DELIVERED,
                'payment_status' => SalesOrder::PAYMENT_STATUS_PENDING,
                'due_date' => now()->addWeek()->toDateString(),
                'items' => [
                    [
                        'product_id' => $product->id,
                        'quantity' => 3,
                        'unit_price' => 1500,
                    ],
                ],
            ]);

        $salesOrder = SalesOrder::firstOrFail();

        $response->assertRedirect(route('sales-orders.receipt', $salesOrder));
        $this->assertDatabaseHas('sales_orders', [
            'id' => $salesOrder->id,
            'total' => 4500,
            'amount_paid' => 0,
        ]);
        $this->assertDatabaseHas('customers', [
            'id' => $customer->id,
            'account_balance' => -4500,
        ]);
    }

    public function test_overpayment_increases_customer_account_balance(): void
    {
        $store = Store::create([
            'name' => 'Main Store',
            'code' => 'MAIN',
            'location' => 'Lagos',
        ]);

        $salesOfficer = User::factory()->salesOfficer()->create([
            'store_id' => $store->id,
        ]);

        $customer = Customer::create([
            'full_name' => 'Prepaid Customer',
            'status' => Customer::STATUS_ACTIVE,
            'account_balance' => 0,
            'created_by' => $salesOfficer->id,
        ]);

        $product = Product::factory()->create([
            'stock_quantity' => 20,
            'selling_price' => 1500,
            'status' => Product::STATUS_ACTIVE,
        ]);

        $response = $this
            ->actingAs($salesOfficer)
            ->post(route('sales-orders.store'), [
                'customer_mode' => 'existing',
                'customer_id' => $customer->id,
                'order_date' => now()->toDateString(),
                'status' => SalesOrder::STATUS_DELIVERED,
                'payment_status' => SalesOrder::PAYMENT_STATUS_PAID,
                'payment_method' => SalesOrder::PAYMENT_METHOD_TRANSFER,
                'amount_paid' => 5000,
                'items' => [
                    [
                        'product_id' => $product->id,
                        'quantity' => 3,
                        'unit_price' => 1500,
                    ],
                ],
            ]);

        $salesOrder = SalesOrder::firstOrFail();

        $response->assertRedirect(route('sales-orders.receipt', $salesOrder));
        $this->assertDatabaseHas('sales_orders', [
            'id' => $salesOrder->id,
            'total' => 4500,
            'amount_paid' => 5000,
        ]);
        $this->assertDatabaseHas('customers', [
            'id' => $customer->id,
            'account_balance' => 500,
        ]);
    }

    public function test_sales_order_consumes_assigned_store_quantity(): void
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

        $customer = Customer::create([
            'full_name' => 'Store Stock Customer',
            'status' => Customer::STATUS_ACTIVE,
            'account_balance' => 0,
            'created_by' => $salesOfficer->id,
        ]);

        $product = Product::factory()->create([
            'stock_quantity' => 20,
            'selling_price' => 1000,
            'status' => Product::STATUS_ACTIVE,
        ]);

        StoreProductQuantity::create([
            'store_id' => $store->id,
            'product_id' => $product->id,
            'quantity' => 6,
        ]);

        $response = $this
            ->actingAs($salesOfficer)
            ->post(route('sales-orders.store'), [
                'customer_mode' => 'existing',
                'customer_id' => $customer->id,
                'order_date' => now()->toDateString(),
                'status' => SalesOrder::STATUS_DELIVERED,
                'payment_status' => SalesOrder::PAYMENT_STATUS_PAID,
                'payment_method' => SalesOrder::PAYMENT_METHOD_CASH,
                'items' => [
                    [
                        'product_id' => $product->id,
                        'quantity' => 4,
                        'unit_price' => 1000,
                    ],
                ],
            ]);

        $salesOrder = SalesOrder::firstOrFail();

        $response->assertRedirect(route('sales-orders.receipt', $salesOrder));
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'stock_quantity' => 16,
        ]);
        $this->assertDatabaseHas('store_product_quantities', [
            'store_id' => $store->id,
            'product_id' => $product->id,
            'quantity' => 2,
        ], 'tenant');
    }

    public function test_sales_officer_cannot_delete_sales_order(): void
    {
        $store = Store::create([
            'name' => 'Main Store',
            'code' => 'MAIN',
            'location' => 'Lagos',
        ]);

        $salesOfficer = User::factory()->salesOfficer()->create([
            'store_id' => $store->id,
        ]);

        $salesOrder = SalesOrder::factory()->create([
            'store_id' => $store->id,
            'user_id' => $salesOfficer->id,
        ]);

        $response = $this
            ->actingAs($salesOfficer)
            ->delete(route('sales-orders.destroy', $salesOrder));

        $response->assertForbidden();
        $this->assertDatabaseHas('sales_orders', [
            'id' => $salesOrder->id,
        ], 'tenant');
    }

    public function test_sales_officer_can_only_update_status_and_payment_fields(): void
    {
        $store = Store::create([
            'name' => 'Main Store',
            'code' => 'MAIN',
            'location' => 'Lagos',
        ]);

        $salesOfficer = User::factory()->salesOfficer()->create([
            'store_id' => $store->id,
        ]);

        $originalCustomer = Customer::create([
            'full_name' => 'Original Customer',
            'status' => Customer::STATUS_ACTIVE,
            'created_by' => $salesOfficer->id,
        ]);

        $otherCustomer = Customer::create([
            'full_name' => 'Other Customer',
            'status' => Customer::STATUS_ACTIVE,
            'created_by' => $salesOfficer->id,
        ]);

        $product = Product::factory()->create([
            'stock_quantity' => 20,
            'selling_price' => 1000,
            'status' => Product::STATUS_ACTIVE,
        ]);

        $salesOrder = SalesOrder::create([
            'order_number' => 'SO-LOCKED-001',
            'customer_id' => $originalCustomer->id,
            'user_id' => $salesOfficer->id,
            'store_id' => $store->id,
            'order_date' => now()->toDateString(),
            'status' => SalesOrder::STATUS_DELIVERED,
            'subtotal' => 2000,
            'tax' => 0,
            'discount' => 0,
            'payment_method' => SalesOrder::PAYMENT_METHOD_CASH,
            'payment_status' => SalesOrder::PAYMENT_STATUS_PAID,
            'total' => 2000,
            'amount_paid' => 2000,
            'notes' => 'Original notes',
        ]);

        $salesOrder->items()->create([
            'product_id' => $product->id,
            'quantity' => 2,
            'unit_price' => 1000,
            'total_price' => 2000,
        ]);

        $response = $this
            ->actingAs($salesOfficer)
            ->put(route('sales-orders.update', $salesOrder), [
                'status' => SalesOrder::STATUS_PENDING,
                'payment_status' => SalesOrder::PAYMENT_STATUS_PENDING,
                'payment_method' => SalesOrder::PAYMENT_METHOD_TRANSFER,
                'customer_id' => $otherCustomer->id,
                'order_date' => now()->addDay()->toDateString(),
                'notes' => 'Changed notes',
            ]);

        $response->assertRedirect(route('sales-orders.receipt', $salesOrder));

        $this->assertDatabaseHas('sales_orders', [
            'id' => $salesOrder->id,
            'customer_id' => $originalCustomer->id,
            'status' => SalesOrder::STATUS_PENDING,
            'payment_status' => SalesOrder::PAYMENT_STATUS_PENDING,
            'payment_method' => null,
            'amount_paid' => 0,
            'notes' => 'Original notes',
        ], 'tenant');
    }

    public function test_saved_customer_discount_only_applies_in_its_assigned_store(): void
    {
        $discountStore = Store::create([
            'name' => 'Discount Store',
            'code' => 'DISC',
            'location' => 'Lagos',
        ]);

        $otherStore = Store::create([
            'name' => 'Other Store',
            'code' => 'OTHR',
            'location' => 'Abuja',
        ]);

        $salesOfficer = User::factory()->salesOfficer()->create([
            'store_id' => $otherStore->id,
        ]);

        $customer = Customer::create([
            'full_name' => 'Store Scoped Discount Customer',
            'status' => Customer::STATUS_ACTIVE,
            'account_balance' => 0,
            'created_by' => $salesOfficer->id,
        ]);

        $product = Product::factory()->create([
            'stock_quantity' => 10,
            'selling_price' => 250,
            'status' => Product::STATUS_ACTIVE,
        ]);

        CustomerProductDiscount::create([
            'customer_id' => $customer->id,
            'product_id' => $product->id,
            'store_id' => $discountStore->id,
            'discount_amount' => 25,
        ]);

        $response = $this
            ->actingAs($salesOfficer)
            ->post(route('sales-orders.store'), [
                'customer_mode' => 'existing',
                'customer_id' => $customer->id,
                'order_date' => now()->toDateString(),
                'status' => SalesOrder::STATUS_DELIVERED,
                'payment_status' => SalesOrder::PAYMENT_STATUS_PAID,
                'payment_method' => SalesOrder::PAYMENT_METHOD_CASH,
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
        $this->assertDatabaseHas('sales_orders', [
            'id' => $salesOrder->id,
            'discount' => 0,
            'total' => 500,
            'store_id' => $otherStore->id,
        ]);
    }
}
