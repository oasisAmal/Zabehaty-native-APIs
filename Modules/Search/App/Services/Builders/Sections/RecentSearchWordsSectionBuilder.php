<?php

namespace Modules\Search\App\Services\Builders\Sections;

use App\Traits\CountryQueryBuilderTrait;
use Modules\Search\App\Services\Builders\Interfaces\SectionBuilderInterface;

class RecentSearchWordsSectionBuilder implements SectionBuilderInterface
{
    use CountryQueryBuilderTrait;

    /**
     * Build recent search words section data
     *
     * @return array
     */
    public function build(): array
    {
        $user = auth('api')->user();
        if (! $user) {
            return [];
        }

        return $this->getCountryConnection()
            ->table('user_search_words')
            ->where('user_id', $user->id)
            ->orderByDesc('updated_at')
            ->orderByDesc('repeats_count')
            ->limit(10)
            ->pluck('word')
            ->toArray();
    }
}
