<?php

namespace Modules\Products\App\Models\Scopes;

use Illuminate\Database\Eloquent\Scope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class ActiveScope implements Scope
{
    public function apply(Builder $builder, Model $model)
    {
        return $builder->whereHas('department')
            ->where('products.is_active', true)
            ->where(function (Builder $q) {
                $q->whereNull('products.shop_id')
                ->orWhereHas('shop');
            })
            ->where('products.is_approved', true)
            ->where(function (Builder $q) {
                $q->where('products.price', '>', 0)
                    ->orWhereHas('subProducts');
            });
    }
}
