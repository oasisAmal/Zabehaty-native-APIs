<?php

namespace Modules\Users\App\Models;

use App\Traits\CountryDatabaseTrait;
use Illuminate\Database\Eloquent\Model;

class UserSearchWord extends Model
{
    use CountryDatabaseTrait;
    
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'user_search_words';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['user_id', 'word', 'repeats_count'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
