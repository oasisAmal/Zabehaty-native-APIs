<?php

namespace Database\Seeders;

use App\Enums\AppCountries;
use App\Models\MainCategory;
use Illuminate\Database\Seeder;
use App\Helpers\DatabaseHelpers;

class MainCategoriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'slug' => '30_mins',
                'name_en' => '30 mins',
                'name_ar' => '30 دقيقة',
                'icon_path' => "https://zabehaty.s3.us-east-2.amazonaws.com/uploads/bc69b2b71df724ffff294bbe47acab15.png",
                'is_active' => true,
                'position' => 1,
            ],
            [
                'slug' => 'zabehaty',
                'name_en' => 'Zabehaty',
                'name_ar' => 'ذبيحتي',
                'icon_path' => "https://zabehaty.s3.us-east-2.amazonaws.com/uploads/7c2d9941edbcd7a01463e24aaa5ee00a.png",
                'is_active' => true,
                'position' => 2,
            ],
            [
                'slug' => 'market',
                'name_en' => 'Market',
                'name_ar' => 'سوق',
                'icon_path' => "https://zabehaty.s3.us-east-2.amazonaws.com/uploads/a5cf59775f78d553d573fb413c088cc0.png",
                'is_active' => true,
                'position' => 3,
            ],
            [
                'slug' => 'auction',
                'name_en' => 'Auction',
                'name_ar' => 'مزاد',
                'icon_path' => "https://zabehaty.s3.us-east-2.amazonaws.com/uploads/372cf52f5f4d93fdef714cbae3b78378.png",
                'is_active' => true,
                'position' => 4,
            ],
        ];

        // Get available countries from the database configuration
        $availableCountries = AppCountries::getValues();

        foreach ($availableCountries as $countryCode) {
            $countryCode = strtolower($countryCode);
            $this->seedForCountry($countryCode, $categories);
        }
    }

    /**
     * Seed categories for a specific country
     *
     * @param string $countryCode
     * @param array $categories
     * @return void
     */
    private function seedForCountry(string $countryCode, array $categories): void
    {
        try {
            DatabaseHelpers::forCountry($countryCode, function () use ($categories) {
                foreach ($categories as $categoryData) {
                    MainCategory::updateOrCreate(
                        [
                            'position' => $categoryData['position']
                        ],
                        $categoryData
                    );
                }
            });

            $this->command->info("Main categories seeded successfully for country: {$countryCode}");
        } catch (\Exception $e) {
            $this->command->error("Failed to seed main categories for country {$countryCode}: " . $e->getMessage());
        }
    }
}
