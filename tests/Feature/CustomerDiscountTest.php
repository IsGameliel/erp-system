<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerDiscountTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_save_product_specific_discounts_for_customer(): void
    {
        $admin = User::factory()->admin()->create();
        $product = Product::factory()->create();
        $store = Store::create([
            'name' => 'Main Store',
            'code' => 'MAIN',
            'location' => 'Lagos',
        ]);

        $response = $this
            ->actingAs($admin)
            ->post(route('customers.store'), [
                'full_name' => 'Discount Customer',
                'status' => Customer::STATUS_ACTIVE,
                'product_discounts' => [
                    $product->id => [
                        'enabled' => '1',
                        'product_id' => $product->id,
                        'store_id' => $store->id,
                        'discount_amount' => 500,
                    ],
                ],
            ]);

        $response->assertRedirect();

        $customer = Customer::query()->where('full_name', 'Discount Customer')->firstOrFail();

        $this->assertDatabaseHas('customer_product_discounts', [
            'customer_id' => $customer->id,
            'product_id' => $product->id,
            'store_id' => $store->id,
            'discount_amount' => 500,
        ]);
    }

    public function test_sales_officer_cannot_set_customer_product_discounts(): void
    {
        $salesOfficer = User::factory()->salesOfficer()->create();
        $product = Product::factory()->create();
        $store = Store::create([
            'name' => 'Main Store',
            'code' => 'MAIN',
            'location' => 'Lagos',
        ]);

        $response = $this
            ->actingAs($salesOfficer)
            ->post(route('customers.store'), [
                'full_name' => 'Regular Customer',
                'status' => Customer::STATUS_ACTIVE,
                'product_discounts' => [
                    $product->id => [
                        'enabled' => '1',
                        'product_id' => $product->id,
                        'store_id' => $store->id,
                        'discount_amount' => 500,
                    ],
                ],
            ]);

        $response->assertSessionHasErrors('product_discounts');
        $this->assertDatabaseMissing('customers', [
            'full_name' => 'Regular Customer',
        ]);
    }
}
