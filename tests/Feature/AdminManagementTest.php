<?php

namespace Tests\Feature;

use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_sales_officer_assigned_to_store(): void
    {
        $admin = User::factory()->admin()->create();
        $store = Store::create([
            'name' => 'Main Store',
            'code' => 'MAIN',
            'location' => 'HQ',
        ]);

        $response = $this
            ->actingAs($admin)
            ->post(route('users.store'), [
                'name' => 'Sales User',
                'email' => 'sales@example.com',
                'role' => User::ROLE_SALES_OFFICER,
                'store_id' => $store->id,
                'password' => 'password',
                'password_confirmation' => 'password',
            ]);

        $response->assertRedirect(route('users.index'));

        $this->assertDatabaseHas('users', [
            'email' => 'sales@example.com',
            'role' => User::ROLE_SALES_OFFICER,
            'store_id' => $store->id,
        ]);
    }

    public function test_admin_can_create_store_and_assign_officers(): void
    {
        $admin = User::factory()->admin()->create();
        $salesOfficer = User::factory()->salesOfficer()->create();
        $procurementOfficer = User::factory()->procurementOfficer()->create();

        $response = $this
            ->actingAs($admin)
            ->post(route('stores.store'), [
                'name' => 'Branch Store',
                'code' => 'BR001',
                'location' => 'Lagos',
                'description' => 'Primary branch',
                'sales_officer_id' => $salesOfficer->id,
                'procurement_officer_id' => $procurementOfficer->id,
            ]);

        $response->assertRedirect(route('stores.index'));

        $store = Store::where('code', 'BR001')->firstOrFail();

        $this->assertDatabaseHas('users', [
            'id' => $salesOfficer->id,
            'store_id' => $store->id,
        ]);

        $this->assertDatabaseHas('users', [
            'id' => $procurementOfficer->id,
            'store_id' => $store->id,
        ]);
    }
}
