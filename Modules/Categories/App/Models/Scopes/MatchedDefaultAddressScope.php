<?php

namespace Modules\Categories\App\Models\Scopes;

use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class MatchedDefaultAddressScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $user = auth('api')->user();
        if (! $user) {
            return;
        }

        $defaultAddress = $user->defaultAddress;
        if (! $defaultAddress) {
            return;
        }

        $builder->whereHas('categoryVisibilities', $this->buildVisibilityConstraint($defaultAddress));
    }

    private function buildVisibilityConstraint(object $defaultAddress): Closure
    {
        return static function (Builder $query) use ($defaultAddress) {
            $query->where('emirate_id', $defaultAddress->emirate_id)
                ->where(function (Builder $regionQuery) use ($defaultAddress) {
                    if ($defaultAddress->region_id !== null) {
                        $regionQuery->whereJsonContains('region_ids', (int) $defaultAddress->region_id);
                    }
                });
        };
    }
}
