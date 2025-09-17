<?php

namespace App\Http\Resources;

use App\Models\Settings;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OnboardingAdsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return $this->getOnboardingAds();
    }

    private function getOnboardingAds()
    {
        $onboardingAds = Settings::where('key', 'on_boarding_screens')->first();
        if ($onboardingAds == null || empty($onboardingAds->value)) {
            return [];
        }

        $data = [];
        foreach ($onboardingAds as $onboardingAd) {
            $data[] = url('uploads/on-boarding-screens/' . $onboardingAd);
        }
        
        return $data;
    }
}
