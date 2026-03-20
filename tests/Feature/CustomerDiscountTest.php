<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerDiscountTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_save_allowed_discount_amount_for_customer(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this
            ->actingAs($admin)
            ->post(route('customers.store'), [
                'full_name' => 'Discount Customer',
                'status' => Customer::STATUS_ACTIVE,
                'discount_amount' => 500,
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('customers', [
            'full_name' => 'Discount Customer',
            'discount_amount' => 500,
        ]);
    }

    public function test_sales_officer_cannot_set_customer_discount_amount(): void
    {
        $salesOfficer = User::factory()->salesOfficer()->create();

        $response = $this
            ->actingAs($salesOfficer)
            ->post(route('customers.store'), [
                'full_name' => 'Regular Customer',
                'status' => Customer::STATUS_ACTIVE,
                'discount_amount' => 500,
            ]);

        $response->assertSessionHasErrors('discount_amount');
        $this->assertDatabaseMissing('customers', [
            'full_name' => 'Regular Customer',
        ]);
    }
}
