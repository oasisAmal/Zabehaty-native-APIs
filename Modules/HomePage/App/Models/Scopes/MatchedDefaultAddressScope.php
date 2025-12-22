<?php

namespace Modules\HomePage\App\Models\Scopes;

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

        $builder->whereJsonContains('emirate_ids', (string) $defaultAddress->emirate_id)
            ->where(
                function ($query) use ($defaultAddress) {
                    $query->whereNull('region_ids')
                        ->orWhereJsonContains('region_ids', (string) $defaultAddress->region_id);
                }
            );
    }
}
