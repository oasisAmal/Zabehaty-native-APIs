<?php

namespace Modules\Products\App\Models\Attributes;

trait ProductAttributes
{
    public function getImageUrlAttribute()
    {
        return $this->thumb ?? null;
    }

    public function getBadgeNameAttribute()
    {
        return $this->badges->first()->name ?? null;
    }

    public function getIsFavoriteAttribute()
    {
        return (bool) false;
    }

    public function getDiscountPercentageAttribute()
    {
        return (float) discountCalc($this->old_price, $this->price);
    }

    public function getQuantitySettingsAttribute()
    {
        return [
            'min' => $this->quantity_min,
            'step' => $this->quantity_step,
            'section_name' => 'zabay7',
        ];
    }

    public function getStockSettingsAttribute()
    {
        if ($this->has_sub_products && $this->subProducts->isNotEmpty()) {
            $allZero = $this->subProducts->every(function($subProduct) {
                return $subProduct->stock === 0;
            });
            if ($allZero) {
                return 0;
            }
        }
        return $this->stock;
    }
}