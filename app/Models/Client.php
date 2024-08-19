<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Order;
use Illuminate\Database\Eloquent\Casts\Attribute;

/**
 * App\Models\Client
 *
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $business_id
 * @property int $saas_user_id
 * @property string $name
 * @property string $surname
 * @property string $telephone
 * @property string $phone1
 * @property string $phone2
 * @property string $state
 * @property string $region
 * @property string $description
 * @property string $map_link
 * @property string $longitude
 * @property string $latitude
 * @property string $email
 * @property string $nomos
 * @property string $afm
 *
 * @property-read int $changes_count
 *
 * @method static \Database\Factories\ClientFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|Client newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Client newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Client query()
 * @method static \Illuminate\Database\Eloquent\Builder|Client whereBusinessId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Client whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Client whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Client whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Client whereMapLink($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Client whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Client wherePhone1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Client wherePhone2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Client whereRegion($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Client whereSaasUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Client whereState($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Client whereSurname($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Client whereTelephone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Client whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Client extends Model
{
    use HasFactory;
    use SoftDeletes;

    const TABLE='client';
    protected $table=self::TABLE;

    protected $fillable=[
        'name',
        'surname',
        "telephone",
        'phone1',
        "phone2",
        "business_id",
        "saas_user_id",
        "state",
        "region",
        "description",
        'longitude',
        "latitude",
        "email",
        "nomos",
        'afm',
        'stars'
    ];


    protected static function boot()
    {
        parent::boot();

        static::updating(function (Client $model) {
            if ($model->isDirty('changes_count')) {
                return;
            }
            $model->changes_count = ($model->changes_count??0)+1;
        });

        static::deleted(function (Client $model) {
            Order::withTrashed()->where('client_id', $model->id)->update(['client_id' => null]);
        });
    }

}
