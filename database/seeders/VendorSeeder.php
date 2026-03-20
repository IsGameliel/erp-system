<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Vendor;
use Illuminate\Database\Seeder;

class VendorSeeder extends Seeder
{
    public function run(): void
    {
        $procurementOfficerId = User::where('role', User::ROLE_PROCUREMENT_OFFICER)->value('id')
            ?? User::where('role', User::ROLE_ADMIN)->value('id');

        Vendor::factory(20)->create([
            'created_by' => $procurementOfficerId,
        ]);
    }
}
