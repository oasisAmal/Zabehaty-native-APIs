<?php

namespace Modules\Cart\App\Services\Product\PriceModifiers;

use Modules\Products\App\Services\AddonSectionItemTransformerService;
use Modules\Cart\App\Interfaces\PriceModifierInterface;

class AddonModifier implements PriceModifierInterface
{
    /**
     * Add prices of selected addon items to the total price
     *
     * @param float $basePrice The current price
     * @param array $data Contains: 'addon_items' => array of addon item IDs
     * @return float
     */
    public function calculate(float $basePrice, array $data): float
    {
        $addonItems = $data['addon_items'] ?? [];

        if (empty($addonItems) || !is_array($addonItems)) {
            return $basePrice;
        }

        $addonPrice = 0.0;
        $transformerService = app(AddonSectionItemTransformerService::class);

        // Get addon items from product
        $product = $data['product'] ?? null;
        if (!$product) {
            return $basePrice;
        }

        // Load addon sections
        $product->loadMissing('addonSectionPivots');

        foreach ($product->addonSectionPivots as $addonSection) {
            // Load itemsPivots for this pivot
            if (!$addonSection->pivot->relationLoaded('itemsPivots')) {
                $addonSection->pivot->loadMissing('itemsPivots');
            }
            
            // Get itemsPivots for this addon section
            $itemsPivots = $addonSection->pivot->itemsPivots ?? collect();
            
            foreach ($itemsPivots as $item) {
                // Check if this item is in the selected addon_items array
                if (in_array($item->product_addon_section_item_id, $addonItems)) {
                    $itemPrice = $transformerService->getPrice($item);
                    if ($itemPrice !== null) {
                        $addonPrice += (float) $itemPrice;
                    }
                }
            }
        }

        return $basePrice + $addonPrice;
    }
}

