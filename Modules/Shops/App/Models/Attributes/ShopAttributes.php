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
}