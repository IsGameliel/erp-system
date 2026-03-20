<?php

namespace Database\Factories;

use App\Models\PurchaseOrder;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<PurchaseOrder>
 */
class PurchaseOrderFactory extends Factory
{
    protected $model = PurchaseOrder::class;

    public function definition(): array
    {
        return [
            'po_number' => 'PO-'.now()->format('YmdHis').Str::upper(Str::random(4)),
            'vendor_id' => Vendor::factory(),
            'user_id' => User::factory()->procurementOfficer(),
            'order_date' => fake()->dateTimeBetween('-6 months', 'now')->format('Y-m-d'),
            'status' => fake()->randomElement([
                PurchaseOrder::STATUS_DRAFT,
                PurchaseOrder::STATUS_APPROVED,
                PurchaseOrder::STATUS_ORDERED,
            ]),
            'subtotal' => 0,
            'tax' => 0,
            'discount' => 0,
            'total' => 0,
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
