<?php

namespace Database\Seeders;

use App\Models\Settings;
use Illuminate\Database\Seeder;
use App\Helpers\DatabaseHelpers;
use Illuminate\Support\Facades\DB;

class InsertSettingsSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $headerId = null;
        DatabaseHelpers::forCountry('ae', function () use (&$headerId) {
            $headerId = DB::table('settings_header')->where('name', 'خصائص التطبيق')->first()->id;
            if (!$headerId) {
                $headerId = DB::table('settings_header')->insert([
                    'name' => 'خصائص التطبيق',
                    'order' => 12,
                ]);
                $headerId = DB::table('settings_header')->where('name', 'خصائص التطبيق')->first()->id;
            }
        });

        Settings::forCountry('ae')->updateOrCreate([
            'key' => 'auth_options',
        ], [
            'alias' => 'طريقة التسجيل الخاصة بالتطبيق',
            'value' => [
                'password_only' => false,
                'otp_only' => false,
                'password_or_otp' => true
            ],
            'type' => 1,
            'category' => 3,
            'lang' => 'ar',
            'header_id' => $headerId,
        ]);

        Settings::forCountry('ae')->updateOrCreate([
            'key' => 'guest_mode',
        ], [
            'alias' => 'الوضع الضيف للتطبيق',
            'value' => true,
            'type' => 3,
            'category' => 3,
            'lang' => 'ar',
            'header_id' => $headerId,
        ]);

        Settings::forCountry('ae')->updateOrCreate([
            'key' => 'story_section_available',
        ], [
            'alias' => 'القسم القصصي متاح للتطبيق',
            'value' => true,
            'type' => 3,
            'category' => 3,
            'lang' => 'ar',
            'header_id' => $headerId,
        ]);

        Settings::forCountry('ae')->updateOrCreate([
            'key' => 'login_screen_settings_image',
        ], [
            'alias' => 'صورة الشاشة التسجيل للتطبيق',
            'value' => '',
            'type' => 5,
            'category' => 3,
            'lang' => 'ar',
            'header_id' => $headerId,
        ]);

        Settings::forCountry('ae')->updateOrCreate([
            'key' => 'login_screen_settings_dialog_opacity',
        ], [
            'alias' => 'نسبة الشاشة التسجيل للتطبيق',
            'value' => '1.0',
            'type' => 1,
            'category' => 3,
            'lang' => 'ar',
            'header_id' => $headerId,
        ]);

        Settings::forCountry('ae')->updateOrCreate([
            'key' => 'create_account_screen_settings_image',
        ], [
            'alias' => 'صورة الشاشة انشاء حساب للتطبيق',
            'value' => '',
            'type' => 5,
            'category' => 3,
            'lang' => 'ar',
            'header_id' => $headerId,
        ]);

        Settings::forCountry('ae')->updateOrCreate([
            'key' => 'create_account_screen_settings_dialog_opacity',
        ], [
            'alias' => 'نسبة الشاشة انشاء حساب للتطبيق',
            'value' => '1.0',
            'type' => 1,
            'category' => 3,
            'lang' => 'ar',
            'header_id' => $headerId,
        ]);

        Settings::forCountry('ae')->updateOrCreate([
            'key' => 'otp_screen_settings_image',
        ], [
            'alias' => 'صورة الشاشة ارسال رمز التحقق للتطبيق',
            'value' => '',
            'type' => 5,
            'category' => 3,
            'lang' => 'ar',
            'header_id' => $headerId,
        ]);

        Settings::forCountry('ae')->updateOrCreate([
            'key' => 'otp_screen_settings_dialog_opacity',
        ], [
            'alias' => 'نسبة الشاشة ارسال رمز التحقق للتطبيق',
            'value' => '1.0',
            'type' => 1,
            'category' => 3,
            'lang' => 'ar',
            'header_id' => $headerId,
        ]);

        Settings::forCountry('ae')->updateOrCreate([
            'key' => 'forgot_screen_settings_image',
        ], [
            'alias' => 'صورة الشاشة نسيت كلمة المرور للتطبيق',
            'value' => '',
            'type' => 5,
            'category' => 3,
            'lang' => 'ar',
            'header_id' => $headerId,
        ]);

        Settings::forCountry('ae')->updateOrCreate([
            'key' => 'forgot_screen_settings_dialog_opacity',
        ], [
            'alias' => 'نسبة الشاشة نسيت كلمة المرور للتطبيق',
            'value' => '1.0',
            'type' => 1,
            'category' => 3,
            'lang' => 'ar',
            'header_id' => $headerId,
        ]);

        Settings::forCountry('ae')->updateOrCreate([
            'key' => 'forgot_password_screen_settings_image',
        ], [
            'alias' => 'صورة الشاشة تعيين كلمة المرور للتطبيق',
            'value' => '',
            'type' => 5,
            'category' => 3,
            'lang' => 'ar',
            'header_id' => $headerId,
        ]);

        Settings::forCountry('ae')->updateOrCreate([
            'key' => 'forgot_password_screen_settings_dialog_opacity',
        ], [
            'alias' => 'نسبة الشاشة تعيين كلمة المرور للتطبيق',
            'value' => '1.0',
            'type' => 1,
            'category' => 3,
            'lang' => 'ar',
            'header_id' => $headerId,
        ]);

        Settings::forCountry('ae')->updateOrCreate([
            'key' => 'password_verification_screen_settings_image',
        ], [
            'alias' => 'صورة الشاشة التحقق من كلمة المرور للتطبيق',
            'value' => '',
            'type' => 5,
            'category' => 3,
            'lang' => 'ar',
            'header_id' => $headerId,
        ]);

        Settings::forCountry('ae')->updateOrCreate([
            'key' => 'password_verification_screen_settings_dialog_opacity',
        ], [
            'alias' => 'نسبة الشاشة التحقق من كلمة المرور للتطبيق',
            'value' => '1.0',
            'type' => 1,
            'category' => 3,
            'lang' => 'ar',
            'header_id' => $headerId,
        ]);

        Settings::forCountry('ae')->updateOrCreate([
            'key' => 'on_boarding_screens',
        ], [
            'alias' => 'اعلانات بعد فتح شاشه التطبيق',
            'value' => '',
            'type' => 1,
            'category' => 3,
            'lang' => 'ar',
            'header_id' => $headerId,
            'is_json' => 1,
            'in_api' => 1,
        ]);

        Settings::forCountry('ae')->updateOrCreate([
            'key' => 'homepage_background_url',
        ], [
            'alias' => 'صورة خلفية الصفحة الرئيسية',
            'value' => '',
            'type' => 5,
            'category' => 3,
            'lang' => 'ar',
            'header_id' => $headerId,
        ]);
    }
}
