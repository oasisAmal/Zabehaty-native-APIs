<?php

namespace Modules\Cart\App\Services\Product;

use Modules\Cart\App\Interfaces\PriceModifierInterface;
use Modules\Cart\App\Services\Product\PriceModifiers\BasePriceModifier;
use Modules\Cart\App\Services\Product\PriceModifiers\QuantityModifier;
use Modules\Cart\App\Services\Product\PriceModifiers\AddonModifier;
use Modules\Cart\App\Services\Product\Modifiers\StockAvailabilityModifier;

class ProductPriceCalculator
{
    /**
     * Array of price modifiers to apply in order
     *
     * @var array<PriceModifierInterface>
     */
    protected array $modifiers;

    /**
     * Stock availability modifier
     *
     * @var StockAvailabilityModifier
     */
    protected StockAvailabilityModifier $stockAvailabilityModifier;

    public function __construct()
    {
        $this->modifiers = [
            new BasePriceModifier(),
            new QuantityModifier(),
            new AddonModifier(),
        ];

        $this->stockAvailabilityModifier = new StockAvailabilityModifier();
    }

    /**
     * Calculate the final product price by applying all modifiers
     *
     * @param array $data Contains: 'product' => Product, 'size_id' => int|null, 'quantity' => int, 'addon_items' => array
     * @return float The final calculated price
     */
    public function calculate(array $data): float
    {
        $price = 0.0;

        // Apply each modifier in sequence
        foreach ($this->modifiers as $modifier) {
            $price = $modifier->calculate($price, $data);
        }

        return $price;
    }

    /**
     * Check if product can be added to cart based on stock
     *
     * @param array $data Contains: 'product' => Product, 'size_id' => int|null
     * @return bool
     */
    public function canBeAddedToCart(array $data): bool
    {
        return $this->stockAvailabilityModifier->canBeAddedToCart($data);
    }
}

