<?php

namespace Modules\Users\App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Laravel\Sanctum\HasApiTokens;
use App\Traits\CountryDatabaseTrait;
use Illuminate\Notifications\Notifiable;
use Spatie\Activitylog\Traits\LogsActivity;
use Modules\Users\App\Models\Scopes\UserScopes;
use Modules\Users\App\Models\Attributes\UserAttributes;
use Modules\Users\App\Models\Relationships\UserRelationships;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens, CountryDatabaseTrait, LogsActivity, SoftDeletes;
    use UserAttributes, UserScopes, UserRelationships;

    protected static $logFillable  = true;
    protected static $logOnlyDirty = true;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'user';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $guarded = [
        'id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_guest' => 'boolean',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Check if user is a guest
     *
     * @return bool
     */
    public function isGuest(): bool
    {
        return $this->is_guest === true;
    }

    /**
     * Check if user is registered (not guest)
     *
     * @return bool
     */
    public function isRegistered(): bool
    {
        return $this->is_guest === false;
    }
}
