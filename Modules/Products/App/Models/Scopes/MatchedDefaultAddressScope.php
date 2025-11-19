<?php

namespace Modules\Products\App\Models\Scopes;

use Illuminate\Database\Eloquent\Scope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class MatchedDefaultAddressScope implements Scope
{
    public function apply(Builder $builder, Model $model)
    {
        $user = auth('api')->user();
        if (! $user) {
            return;
        }

        $defaultAddress = $user->defaultAddress;
        if (! $defaultAddress) {
            return;
        }

        // $builder->where(function (Builder $q) use ($defaultAddress) {
        //     $q->whereHas('productBranches.branchModel', function (Builder $q) use ($defaultAddress) {
        //         return $q->where('emirate_id', $defaultAddress->emirate_id)
        //             ->where(function (Builder $q) use ($defaultAddress) {
        //                 $q->whereNull('region_id')
        //                     ->orWhere('region_id', $defaultAddress->region_id);
        //             });
        //     });

        //     $q->orWhereHas('productBranches.shopBranchModel', function (Builder $q) use ($defaultAddress) {
        //         return $q->where('emirate_id', $defaultAddress->emirate_id)
        //             ->where(function (Builder $q) use ($defaultAddress) {
        //                 $q->whereNull('region_id')
        //                     ->orWhere('region_id', $defaultAddress->region_id);
        //             });
        //     });

        //     $q->orDoesntHave('productBranches');
        // });
    }
}
