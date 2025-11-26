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
}