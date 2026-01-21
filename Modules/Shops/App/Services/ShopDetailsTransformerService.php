<?php

namespace Modules\Shops\App\Services;

class ShopDetailsTransformerService
{
   /**
    * Get payment badges
     *
     * @param object $shop
     * @return array
    */
    public function getPaymentBadges(object $shop)
    {
        return [
            'tamara',
            'tabby',
        ];
    }
}
