<?php

namespace Modules\Users\App\Models;

use Modules\Shops\App\Models\Shop;
use App\Traits\CountryDatabaseTrait;
use Illuminate\Database\Eloquent\Model;
use Modules\Products\App\Models\Product;
use Modules\Categories\App\Models\Category;

class UserVisit extends Model
{
    use CountryDatabaseTrait;
    
    protected $table = 'user_visits';

    protected $fillable = ['user_id', 'product_id', 'shop_id', 'category_id', 'visit_count'];

    public $timestamps = true;

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function shop()
    {
        return $this->belongsTo(Shop::class, 'shop_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }
}
