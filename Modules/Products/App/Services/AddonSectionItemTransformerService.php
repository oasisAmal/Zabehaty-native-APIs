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
        return handleMediaVideoOrImage($media);
    }
}

