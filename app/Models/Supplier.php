<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    protected $table = 'suppliers';

    protected $fillable = [
        'name',
    ];

    public function emirates()
    {
        return $this->hasMany(Emirate::class);
    }

    public function regions()
    {
        return $this->hasMany(Region::class);
    }

    public function supplierLinks()
    {
        return $this->hasMany(SupplierLink::class);
    }
}
