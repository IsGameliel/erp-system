<?php

namespace Tests\Feature;

use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_open_owner_user_create_page_without_loading_tenant_stores(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();

        $response = $this
            ->actingAs($superAdmin)
            ->get(route('owner.users.create'));

        $response->assertOk();
        $response->assertViewHas('stores', fn ($stores) => $stores->isEmpty());
    }

    public function test_super_admin_can_list_owner_users_without_loading_tenant_store_relation(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $store = Store::create([
            'name' => 'Owner Route Store',
            'code' => 'OWNER',
            'location' => 'HQ',
        ]);

        User::factory()->salesOfficer()->create([
            'store_id' => $store->id,
        ]);

        $response = $this
            ->actingAs($superAdmin)
            ->get(route('owner.users.index'));

        $response->assertOk();
    }

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
