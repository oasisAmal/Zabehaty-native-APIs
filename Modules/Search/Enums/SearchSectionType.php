<?php

namespace Modules\Search\Enums;

use BenSampo\Enum\Enum;

final class SearchSectionType extends Enum
{
    const RECENT_SEARCH_WORDS = 'recent_search_words';
    const BANNERS = 'banners';
    const RECENTLY_VIEWED_PRODUCTS = 'recently_viewed_products';
}
