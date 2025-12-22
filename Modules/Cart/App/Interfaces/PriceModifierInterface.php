<?php

namespace Modules\Cart\App\Interfaces;

interface PriceModifierInterface
{
    /**
     * Calculate and modify the price based on the modifier logic
     *
     * @param float $basePrice The current price before modification
     * @param array $data The data needed for calculation (product, size_id, quantity, addon_items, etc.)
     * @return float The modified price
     */
    public function calculate(float $basePrice, array $data): float;
}

