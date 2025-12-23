<?php

namespace Modules\Products\App\Services;

class AddonSectionItemTransformerService
{
    /**
     * Get addon section item price
     *
     * @param mixed $item
     * @return float|null
     */
    public function getPrice($item): ?float
    {
        $price = null;
        if (isset($item->pivot->price) && $item->pivot->price) {
            $price = (float) $item->pivot->price;
        } elseif (isset($item->price) && $item->price) {
            $price = (float) $item->price;
        }
        return $price ?? null;
    }

    /**
     * Get addon section item media
     *
     * @param mixed $item
     * @return array
     */
    public function getMedia($item): array
    {
        $media = $item->media ?? [];
        if (is_string($media)) {
            $media = json_decode($media, true) ?? [];
        }
        if (!is_array($media)) {
            return [];
        }

        return array_values(array_filter(array_map(function ($entry) {
            if (!is_string($entry) || $entry === '') {
                return null;
            }

            return [
                'media_type' => $this->isVideo($entry) ? 'video' : 'image',
                'media_url' => $entry,
            ];
        }, $media)));
    }

    /**
     * Determine if media entry is a video based on extension.
     */
    private function isVideo(string $entry): bool
    {
        $extension = strtolower(pathinfo(parse_url($entry, PHP_URL_PATH) ?? '', PATHINFO_EXTENSION));

        return in_array($extension, ['mp4', 'mov', 'avi', 'mkv', 'webm'], true);
    }
}

