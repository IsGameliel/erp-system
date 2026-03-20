<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Vendor;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Vendor>
 */
class VendorFactory extends Factory
{
    protected $model = Vendor::class;

    public function definition(): array
    {
        return [
            'company_name' => fake()->company(),
            'contact_person' => fake()->name(),
            'email' => fake()->boolean(70) ? fake()->unique()->safeEmail() : null,
            'phone' => fake()->optional()->phoneNumber(),
            'address' => fake()->optional()->address(),
            'category' => fake()->randomElement(['raw_materials', 'equipment', 'office_supplies']),
            'status' => fake()->randomElement(Vendor::STATUSES),
            'notes' => fake()->optional()->sentence(),
            'created_by' => User::factory()->procurementOfficer(),
        ];
    }
}
