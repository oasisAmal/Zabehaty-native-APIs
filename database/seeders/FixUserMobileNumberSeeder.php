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
        $usersQuery = User::forCountry('ae')->orderBy('id', 'asc');

        $progressBar = null;
        if ($this->command) {
            $progressBar = $this->command->getOutput()->createProgressBar($usersQuery->count());
            $progressBar->start();
        }

        $usersQuery
            // ->where('id', 108) // test user
            // ->where('id', 89010) // test user
            ->chunk(100, function ($users) use ($progressBar) {
                foreach ($users as $user) {
                    $userMobiles = $this->getValidMobiles($user->mobile);
                    $userMobile = $userMobiles[0] ?? null;
                    if (count($userMobiles) === 1) {
                        $user->mobile = format_mobile_number_to_database($userMobile);
                    }
                    $user->country_code = $userMobile ? getCountryCodeNumberFromMobile($userMobile) : null;
                    $user->country_symbol = $userMobile ? getCountryCodeFromMobile($userMobile) : null;
                    $user->save();

                    $user->addresses()->each(function ($address) {
                        $addressMobiles = $this->getValidMobiles($address->mobile);
                        $addressMobile = $addressMobiles[0] ?? null;
                        if (count($addressMobiles) === 1) {
                            $address->mobile = format_mobile_number_to_database($addressMobile);
                        }
                        $address->country_code = $addressMobile ? getCountryCodeFromMobile($addressMobile) : null;
                        $address->save();
                    });

                    if ($progressBar) {
                        $progressBar->advance();
                    }
                }
            });

        if ($progressBar) {
            $progressBar->finish();
            $this->command->getOutput()->newLine();
        }
    }

    private function getValidMobiles(?string $rawMobile): array
    {
        if (!$rawMobile) {
            return [];
        }

        $normalized = str_replace([';', '|', '/', '\\'], ',', $rawMobile);
        $parts = preg_split('/[,\s]+/', $normalized, -1, PREG_SPLIT_NO_EMPTY);

        $validMobiles = [];
        foreach ($parts as $part) {
            $candidate = preg_replace('/[^0-9+]/', '', $part);
            if ($candidate === '') {
                continue;
            }

            $countrySymbol = getCountryCodeFromMobile($candidate);
            $countryCode = getCountryCodeNumberFromMobile($candidate);

            if (($countrySymbol || $countryCode) && !in_array($candidate, $validMobiles, true)) {
                $validMobiles[] = $candidate;
            }
        }

        return $validMobiles;
    }
}
