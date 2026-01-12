<?php

namespace Modules\DynamicShops\Enums;

use BenSampo\Enum\Enum;

final class DynamicShopSectionType extends Enum
{
    const BANNERS = 'banners';
    const SHOPS = 'shops';
    const PRODUCTS = 'products';
    const MENU_ITEMS = 'menu_items';
    const LIMITED_TIME_OFFERS = 'limited_time_offers';
}
