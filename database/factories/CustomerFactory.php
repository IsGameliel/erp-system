<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Customer>
 */
class CustomerFactory extends Factory
{
    protected $model = Customer::class;

    public function definition(): array
    {
        return [
            'full_name' => fake()->name(),
            'business_name' => fake()->optional()->company(),
            'email' => fake()->boolean(70) ? fake()->unique()->safeEmail() : null,
            'phone' => fake()->optional()->phoneNumber(),
            'address' => fake()->optional()->address(),
            'customer_type' => fake()->randomElement(['retail', 'wholesale', 'enterprise']),
            'account_balance' => 0,
            'status' => fake()->randomElement(Customer::STATUSES),
            'notes' => fake()->optional()->sentence(),
            'created_by' => User::factory()->salesOfficer(),
        ];
    }
}
