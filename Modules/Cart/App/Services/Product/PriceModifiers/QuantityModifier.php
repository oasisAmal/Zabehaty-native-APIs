<?php

namespace Modules\Cart\App\Services\Product\PriceModifiers;

use Modules\Cart\App\Interfaces\PriceModifierInterface;

class QuantityModifier implements PriceModifierInterface
{
    /**
     * Multiply the price by the selected quantity
     * Quantity must respect quantity_min and quantity_step from product
     *
     * @param float $basePrice The current price
     * @param array $data Contains: 'quantity' => float, 'product' => Product
     * @return float
     */
    public function calculate(float $basePrice, array $data): float
    {
        $product = $data['product'] ?? null;
        $requestedQuantity = isset($data['quantity']) ? (float) $data['quantity'] : 1.0;

        if (!$product) {
            return $basePrice * $requestedQuantity;
        }

        // Get quantity settings from product
        $quantityMin = (float) ($product->quantity_min ?? 1.0);
        $quantityStep = (float) ($product->quantity_step ?? 1.0);

        // Ensure quantity_min and quantity_step are positive
        if ($quantityMin <= 0) {
            $quantityMin = 1.0;
        }
        if ($quantityStep <= 0) {
            $quantityStep = 1.0;
        }

        // Calculate valid quantity based on min and step
        $validQuantity = $this->calculateValidQuantity($requestedQuantity, $quantityMin, $quantityStep);

        // quantity_min represents the first unit, basePrice is the price for quantity_min
        // Calculate number of units: quantity / quantity_min
        $numberOfUnits = $validQuantity / $quantityMin;

        // Final price = basePrice * number of units
        return $basePrice * $numberOfUnits;
    }

    /**
     * Calculate valid quantity based on min and step
     * Quantity must be: quantity_min + (n * quantity_step) where n >= 0
     *
     * @param float $requestedQuantity
     * @param float $quantityMin
     * @param float $quantityStep
     * @return float
     */
    protected function calculateValidQuantity(float $requestedQuantity, float $quantityMin, float $quantityStep): float
    {
        // If requested quantity is less than minimum, use minimum
        if ($requestedQuantity < $quantityMin) {
            return $quantityMin;
        }

        // Calculate how many steps above the minimum
        $stepsAboveMin = ($requestedQuantity - $quantityMin) / $quantityStep;
        
        // Round to nearest step (round up if exactly in the middle)
        $roundedSteps = round($stepsAboveMin);
        
        // Calculate valid quantity: min + (rounded steps * step)
        $validQuantity = $quantityMin + ($roundedSteps * $quantityStep);

        // Ensure it's at least the minimum
        return max($validQuantity, $quantityMin);
    }
}

