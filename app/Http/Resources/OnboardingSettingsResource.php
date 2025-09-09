<?php

namespace App\Http\Resources;

use App\Models\Settings;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OnboardingSettingsResource extends JsonResource
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
            'login_background_image' => $this->getLoginBackgroundImage($settings),
            'forgot_background_image' => $this->getForgotBackgroundImage($settings),
            'create_account_background_image' => $this->getCreateAccountBackgroundImage($settings),
            'otp_background_image' => $this->getOtpBackgroundImage($settings),
            'forgot_password_background_image' => $this->getForgotPasswordBackgroundImage($settings),
            'dailog_opacity' => $this->getDailogOpacity($settings),
            'ads' => $this->getAds($settings),
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
            return 'both';
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

    private function getLoginBackgroundImage($settings)
    {
        return $settings['login_background_image'] ?? '';
    }

    private function getForgotBackgroundImage($settings)
    {
        return $settings['forgot_background_image'] ?? '';
    }

    private function getCreateAccountBackgroundImage($settings)
    {
        return $settings['create_account_background_image'] ?? '';
    }

    private function getOtpBackgroundImage($settings)
    {
        return $settings['otp_background_image'] ?? '';
    }

    private function getForgotPasswordBackgroundImage($settings)
    {
        return $settings['forgot_password_background_image'] ?? '';
    }

    private function getDailogOpacity($settings)
    {
        return $settings['dailog_opacity'] ?? 0.4;
    }

    private function getAds($settings)
    {
        return $settings['ads'] ?? [];
    }
}
