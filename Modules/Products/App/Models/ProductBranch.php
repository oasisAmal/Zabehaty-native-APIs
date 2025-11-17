<?php
namespace Modules\Products\App\Models;

use App\Models\Branch;
use Illuminate\Database\Eloquent\Model;
use Modules\Shops\App\Models\ShopBranch;

class ProductBranch extends Model
{
    protected $table = 'product_branches';
    protected $fillable = ['product_id','branch_id','branch_type'];

    public function branch()
    {
        return $this->morphTo();
    }

    public function branchModel()
    {
        return $this->belongsTo(Branch::class,'branch_id')->where('branch_type' , 'App\Models\Branch');
    }
    
    public function shopBranchModel()
    {
        return $this->belongsTo(ShopBranch::class,'branch_id')->where('branch_type' , 'App\Models\ShopBranch');
    }
}
