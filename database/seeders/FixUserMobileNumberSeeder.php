<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Users\App\Models\User;

class FixUserMobileNumberSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::forCountry('ae')->orderBy('id', 'asc')
            // ->where('id', 89007) // test user
            // ->where('id', 89010) // test user
            ->chunk(100, function ($users) {
                foreach ($users as $user) {
                    $user->mobile = format_mobile_number_to_database($user->mobile);
                    $user->country_code = getCountryCodeNumberFromMobile($user->mobile);
                    $user->country_symbol = getCountryCodeFromMobile($user->mobile);
                    $user->save();

                    $user->addresses()->each(function ($address) {
                        $address->mobile = format_mobile_number_to_database($address->mobile);
                        $address->country_code = getCountryCodeFromMobile($address->mobile);
                        $address->save();
                    });
                }
            });
    }
}
