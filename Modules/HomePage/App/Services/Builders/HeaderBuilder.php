<?php

namespace Modules\HomePage\App\Services\Builders;

use App\Models\MainCategory;
use App\Models\Settings;

class HeaderBuilder
{
    /**
     * Build header data
     *
     * @return array
     */
    public function build(): array
    {
        return [
            'background_url' => $this->getBackgroundUrl(),
            'main_categories' => $this->getMainCategories(),
            'user_stories' => $this->getUserStories(),
        ];
    }

    /**
     * Get background URL from settings
     *
     * @return string
     */
    private function getBackgroundUrl(): string
    {
        $backgroundUrl = Settings::where('key', 'homepage_background_url')->value('value');

        if (!$backgroundUrl || $backgroundUrl === '""') {
            return "";
        }

        // If it's already a full URL, return as is
        if (filter_var($backgroundUrl, FILTER_VALIDATE_URL)) {
            return $backgroundUrl;
        }

        return "";
    }

    /**
     * Get active main categories
     *
     * @return array
     */
    private function getMainCategories(): array
    {
        return MainCategory::ordered()
            ->get()
            ->map(function ($category) {
                return [
                    'slug' => $category->slug,
                    'name' => $category->name,
                    'icon_url' => $category->icon_path,
                ];
            })->toArray();
    }

    /**
     * Get active user stories
     *
     * @return array
     */
    private function getUserStories(): array
    {
        return [];
    }
}
