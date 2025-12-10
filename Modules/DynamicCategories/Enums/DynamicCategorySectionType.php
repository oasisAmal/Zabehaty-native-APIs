<?php

namespace Modules\DynamicCategories\Enums;

use BenSampo\Enum\Enum;

final class DynamicCategorySectionType extends Enum
{
    const BANNERS = 'banners';
    const SHOPS = 'shops';
    const PRODUCTS = 'products';
    const MENU_ITEMS = 'menu_items';
    const LIMITED_TIME_OFFERS = 'limited_time_offers';
}

