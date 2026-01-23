<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Users\App\Models\UserAddress;

class DeactivateUserAddressSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        UserAddress::forCountry('ae')->orderBy('id', 'asc')
            ->where('is_active', 1)
            ->whereNull('region_id')
            ->orWhereNull('emirate_id')
            ->chunk(100, function ($addresses) {
                foreach ($addresses as $address) {
                    $address->update(['is_active' => 0]);
                }
            });
    }
}
