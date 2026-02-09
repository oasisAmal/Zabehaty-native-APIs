<?php

namespace Modules\Users\App\Services;

use App\Traits\CountryQueryBuilderTrait;

class UserService
{
    use CountryQueryBuilderTrait;

    /**
     * Get the count of products in the user's favorites.
     * 
     * @param int|null $userId
     * @return int
     */
    public function getFavoritesCount(?int $userId): int
    {
        if ($userId === null) {
            return 0;
        }

        return $this->getCountryConnection()
            ->table('favourites')
            ->where('user_id', $userId)
            ->count();
    }
}
