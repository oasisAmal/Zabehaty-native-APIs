<?php

namespace Modules\Shops\App\Models\Attributes;

trait ShopAttributes
{
    public function getPaymentBadgesAttribute()
    {
        return [
            'tamara',
            'tabby',
        ];
    }

    public function getFirstParentCategoryAttribute()
    {
        return $this->categories()->whereNull('categories.parent_id')->first();
    }
}