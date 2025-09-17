<?php

namespace App\Http\Resources;

use App\Models\Settings;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AppSettingsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $settings = $this->getSettings();
        return [
            'auth_options' => $this->getAuthOptions($settings),
            'guest_mode' => $this->getGuestMode($settings),
            'login_screen_settings' => $this->getLoginScreenSettings($settings),
            'forgot_screen_settings' => $this->getForgotScreenSettings($settings),
            'create_account_screen_settings' => $this->getCreateAccountScreenSettings($settings),
            'otp_screen_settings' => $this->getOtpScreenSettings($settings),
            'forgot_password_screen_settings' => $this->getForgotPasswordScreenSettings($settings),
        ];
    }

    private function getSettings()
    {
        return Settings::pluck('value', 'key')->toArray();
    }

    private function getAuthOptions($settings)
    {
        $authOptions = $settings['auth_options'] ?? [];
        if (empty($authOptions)) {
            return '';
        }

        if ($authOptions['password_or_otp']) {
            return 'password_or_otp';
        }

        if ($authOptions['password_only']) {
            return 'password';
        }

        if ($authOptions['otp_only']) {
            return 'otp';
        }

        return '';
    }

    private function getGuestMode($settings)
    {
        return $settings['guest_mode'] ?? false;
    }

    private function getLoginScreenSettings($settings)
    {
        return [
            'background_image' => $settings['login_screen_settings_image'] ?? '',
            'dialog_opacity' => (float) ($settings['login_screen_settings_dialog_opacity'] ?? 1.0),
        ];
    }

    private function getForgotScreenSettings($settings)
    {
        return [
            'background_image' => $settings['forgot_screen_settings_image'] ?? '',
            'dialog_opacity' => (float) ($settings['forgot_screen_settings_dialog_opacity'] ?? 1.0),
        ];
    }

    private function getCreateAccountScreenSettings($settings)
    {
        return [
            'background_image' => $settings['create_account_screen_settings_image'] ?? '',
            'dialog_opacity' => (float) ($settings['create_account_screen_settings_dialog_opacity'] ?? 1.0),
        ];
    }

    private function getOtpScreenSettings($settings)
    {
        return [
            'background_image' => $settings['otp_screen_settings_image'] ?? '',
            'dialog_opacity' => (float) ($settings['otp_screen_settings_dialog_opacity'] ?? 1.0),
        ];
    }

    private function getForgotPasswordScreenSettings($settings)
    {
        return [
            'background_image' => $settings['forgot_password_screen_settings_image'] ?? '',
            'dialog_opacity' => (float) ($settings['forgot_password_screen_settings_dialog_opacity'] ?? 1.0),
        ];
    }
}
