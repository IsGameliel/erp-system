<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ActivityLogMonitoringTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_monitored_staff_activity_history(): void
    {
        $admin = User::factory()->admin()->create();
        $salesOfficer = User::factory()->salesOfficer()->create();
        $procurementOfficer = User::factory()->procurementOfficer()->create();

        ActivityLog::create([
            'user_id' => $admin->id,
            'user_role' => User::ROLE_ADMIN,
            'action' => 'updated',
            'module' => 'customers',
            'description' => 'Updated customer Precious.',
        ]);

        ActivityLog::create([
            'user_id' => $salesOfficer->id,
            'user_role' => User::ROLE_SALES_OFFICER,
            'action' => 'updated',
            'module' => 'sales_orders',
            'description' => 'Updated sales order SO-001.',
        ]);

        ActivityLog::create([
            'user_id' => $procurementOfficer->id,
            'user_role' => User::ROLE_PROCUREMENT_OFFICER,
            'action' => 'updated',
            'module' => 'purchase_orders',
            'description' => 'Updated purchase order PO-001.',
        ]);

        $response = $this
            ->actingAs($admin)
            ->get(route('activity-logs.index'));

        $response->assertOk();
        $response->assertSee('Updated customer Precious.');
        $response->assertSee('Updated sales order SO-001.');
        $response->assertSee('Updated purchase order PO-001.');
    }

    public function test_sales_officer_cannot_view_admin_activity_history(): void
    {
        $salesOfficer = User::factory()->salesOfficer()->create();

        $response = $this
            ->actingAs($salesOfficer)
            ->get(route('activity-logs.index'));

        $response->assertForbidden();
    }

    public function test_admin_activity_history_displays_field_level_changes(): void
    {
        $admin = User::factory()->admin()->create();

        ActivityLog::create([
            'user_id' => $admin->id,
            'user_role' => User::ROLE_ADMIN,
            'action' => 'updated',
            'module' => 'customers',
            'description' => 'Updated customer Precious.',
            'old_values' => [
                'status' => 'active',
                'phone' => '08000000000',
            ],
            'new_values' => [
                'status' => 'inactive',
                'phone' => '09000000000',
            ],
        ]);

        $response = $this
            ->actingAs($admin)
            ->get(route('activity-logs.index'));

        $response->assertOk();
        $response->assertSee('status', false);
        $response->assertSee('phone', false);
        $response->assertSee('active');
        $response->assertSee('inactive');
        $response->assertSee('08000000000');
        $response->assertSee('09000000000');
    }
}
