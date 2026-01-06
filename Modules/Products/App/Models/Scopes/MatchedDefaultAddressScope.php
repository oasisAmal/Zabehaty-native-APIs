<?php

namespace Modules\Products\App\Models\Scopes;

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

        $visibilityConstraint = $this->buildVisibilityConstraint($defaultAddress);

        $builder->where(function (Builder $query) use ($visibilityConstraint) {
            $query->whereHas('productVisibilities', $visibilityConstraint)
            ->where(function (Builder $q) use ($visibilityConstraint) {
                $q->whereHas('shop', function (Builder $shopQuery) use ($visibilityConstraint) {
                    $shopQuery->whereHas('shopVisibilities', $visibilityConstraint);
                })
                ->orWhereNull('products.shop_id');
            })
                ->whereHas('category', function (Builder $categoryQuery) use ($visibilityConstraint) {
                    $categoryQuery->whereHas('categoryVisibilities', $visibilityConstraint);
                });
        });
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
