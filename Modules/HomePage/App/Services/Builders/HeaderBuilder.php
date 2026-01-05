<?php

namespace Modules\HomePage\App\Services\Builders;

use App\Models\Settings;
use App\Models\MainCategory;
use Modules\HomePage\App\Models\HomePageBackgroud;

class HeaderBuilder
{
    protected array $settings;

    public function __construct()
    {
        $this->settings = Settings::pluck('value', 'key')->toArray();
    }

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
            'story_section_available' => $this->storySectionAvailable(),
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
        return HomePageBackgroud::first()->background_image_url ?? '';
    }

    /**
     * Get active main categories
     *
     * @return array
     */
    private function getMainCategories(): array
    {
        return MainCategory::ordered()
            ->active()
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
     * Check if user stories are enabled
     *
     * @return bool
     */
    private function storySectionAvailable(): bool
    {
        return isset($this->settings['story_section_available']) ? (bool) $this->settings['story_section_available'] : false;
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
