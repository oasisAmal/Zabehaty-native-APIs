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
            'password_verification_screen_settings' => $this->getPasswordVerificationScreenSettings($settings),
        ];
    }

    private function getSettings()
    {
        return Settings::pluck('value', 'key')->toArray();
    }

    private function getAuthOptions($settings)
    {
        $settings = isset($settings['auth_options']) ? $settings['auth_options'] : '';
        $authOptions = json_decode($settings, true);
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
        $settings = isset($settings['guest_mode']) ? $settings['guest_mode'] : '';
        return (bool) $settings;
    }

    private function getLoginScreenSettings($settings)
    {
        $backgroundImage = isset($settings['login_screen_settings_image']) ? $settings['login_screen_settings_image'] : '';
        if (isset($settings['login_screen_settings_image']) && $settings['login_screen_settings_image'] == '""') {
            $backgroundImage = '';
        }
        return [
            'background_image' => $backgroundImage,
            'dialog_opacity' => (float) ($settings['login_screen_settings_dialog_opacity'] ?? 1.0),
        ];
    }

    private function getForgotScreenSettings($settings)
    {
        $backgroundImage = isset($settings['forgot_screen_settings_image']) ? $settings['forgot_screen_settings_image'] : '';
        if (isset($settings['forgot_screen_settings_image']) && $settings['forgot_screen_settings_image'] == '""') {
            $backgroundImage = '';
        }
        return [
            'background_image' => $backgroundImage,
            'dialog_opacity' => (float) ($settings['forgot_screen_settings_dialog_opacity'] ?? 1.0),
        ];
    }

    private function getCreateAccountScreenSettings($settings)
    {
        $backgroundImage = isset($settings['create_account_screen_settings_image']) ? $settings['create_account_screen_settings_image'] : '';
        if (isset($settings['create_account_screen_settings_image']) && $settings['create_account_screen_settings_image'] == '""') {
            $backgroundImage = '';
        }
        return [
            'background_image' => $backgroundImage,
            'dialog_opacity' => (float) ($settings['create_account_screen_settings_dialog_opacity'] ?? 1.0),
        ];
    }

    private function getOtpScreenSettings($settings)
    {
        $backgroundImage = isset($settings['otp_screen_settings_image']) ? $settings['otp_screen_settings_image'] : '';
        if (isset($settings['otp_screen_settings_image']) && $settings['otp_screen_settings_image'] == '""') {
            $backgroundImage = '';
        }
        return [
            'background_image' => $backgroundImage,
            'dialog_opacity' => (float) ($settings['otp_screen_settings_dialog_opacity'] ?? 1.0),
        ];
    }

    private function getForgotPasswordScreenSettings($settings)
    {
        $backgroundImage = isset($settings['forgot_password_screen_settings_image']) ? $settings['forgot_password_screen_settings_image'] : '';
        if (isset($settings['forgot_password_screen_settings_image']) && $settings['forgot_password_screen_settings_image'] == '""') {
            $backgroundImage = '';
        }
        return [
            'background_image' => $backgroundImage,
            'dialog_opacity' => (float) ($settings['forgot_password_screen_settings_dialog_opacity'] ?? 1.0),
        ];
    }

    private function getPasswordVerificationScreenSettings($settings)
    {
        $backgroundImage = isset($settings['password_verification_screen_settings_image']) ? $settings['password_verification_screen_settings_image'] : '';
        if (isset($settings['password_verification_screen_settings_image']) && $settings['password_verification_screen_settings_image'] == '""') {
            $backgroundImage = '';
        }

        return [
            'background_image' => $backgroundImage,
            'dialog_opacity' => (float) ($settings['password_verification_dialog_opacity'] ?? 1.0),
        ];
    }
}
