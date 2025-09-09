<?php

namespace Modules\Auth\Database\Seeders;

use App\Models\Settings;
use Illuminate\Database\Seeder;

class InsertAuthOptionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Settings::create([
            'key' => 'auth_options',
            'value' => [
                'password_only' => false,
                'otp_only' => false,
                'password_or_otp' => true
            ],
            'type' => 0,
            'category' => 0,
            'alias' => 'تسجيل الدخول الخاصة بالتطبيق',
            'lang' => '',
            'order' => 1,
            'is_editable' => 1,
            'header_id' => 0,
            'is_json' => 1,
            'in_api' => 1
        ]);
    }
}
