<?php

namespace Modules\Shops\App\Models\Relationships;

trait ShopRelationships
{
    public function emirate()
    {
        return $this->belongsTo(Emirate::class, 'emirate_id');
    }

    public function region()
    {
        return $this->belongsTo(Region::class, 'region_id');
    }

    public function shop()
    {
        return $this->belongsTo(Shop::class, 'shop_id');
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function maslakh()
    {
        return $this->belongsTo(Maslakh::class, 'maslakh_id');
    }

    public function checkoutSettings()
    {
        return $this->morphMany(CheckoutSettingsPivot::class, 'parent');
    }
}
