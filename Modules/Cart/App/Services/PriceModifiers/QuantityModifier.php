<?php

namespace Modules\Cart\App\Services\PriceModifiers;

use Modules\Cart\App\Interfaces\PriceModifierInterface;

class QuantityModifier implements PriceModifierInterface
{
    /**
     * Multiply the price by the selected quantity
     *
     * @param float $basePrice The current price
     * @param array $data Contains: 'quantity' => int
     * @return float
     */
    public function calculate(float $basePrice, array $data): float
    {
        $quantity = isset($data['quantity']) ? (int) $data['quantity'] : 1;

        // Ensure quantity is at least 1
        if ($quantity < 1) {
            $quantity = 1;
        }

        return $basePrice * $quantity;
    }
}

