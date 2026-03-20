<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        $salesOfficerId = User::where('role', User::ROLE_SALES_OFFICER)->value('id')
            ?? User::where('role', User::ROLE_ADMIN)->value('id');

        Customer::factory(20)->create([
            'created_by' => $salesOfficerId,
        ]);
    }
}
