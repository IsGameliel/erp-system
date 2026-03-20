<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\SalesOrder;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<SalesOrder>
 */
class SalesOrderFactory extends Factory
{
    protected $model = SalesOrder::class;

    public function definition(): array
    {
        return [
            'order_number' => 'SO-'.now()->format('YmdHis').Str::upper(Str::random(4)),
            'customer_id' => Customer::factory(),
            'user_id' => User::factory()->salesOfficer(),
            'order_date' => fake()->dateTimeBetween('-6 months', 'now')->format('Y-m-d'),
            'status' => fake()->randomElement([
                SalesOrder::STATUS_PENDING,
                SalesOrder::STATUS_CONFIRMED,
                SalesOrder::STATUS_SHIPPED,
            ]),
            'subtotal' => 0,
            'tax' => 0,
            'discount' => 0,
            'total' => 0,
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
