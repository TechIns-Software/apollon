<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

/**
 * Api-Only access User
 * This user has asscess to API only.
 *
 * @package App\Models
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property int $business_id
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SaasUser newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SaasUser newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SaasUser query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SaasUser whereBusinessId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SaasUser whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SaasUser whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SaasUser whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SaasUser whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SaasUser whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SaasUser wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SaasUser whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SaasUser whereUpdatedAt($value)
 */
class SaasUser extends Authenticatable
{
    use HasFactory, Notifiable,HasApiTokens;

    const TABLE  = 'saas_user';
    protected $table=self::TABLE;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'business_id'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function business()
    {
        return $this->hasOne(Business::class,'id','business_id');
    }
}
