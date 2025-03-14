<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $name
 * @property bool $is_active Wheter Business' subscription is Active or not
 * @property string|null $expiration_date Subscription Expiration Date
 * @property string|null $vat Business AFM
 * @property string|null $doy Business Tex office (DOY)
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Business newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Business newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Business query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Business whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Business whereDoy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Business whereExpirationDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Business whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Business whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Business whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Business whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Business whereVat($value)
 */
class Business extends Model
{
    use HasFactory;

    const TABLE = 'business';
    protected $table=self::TABLE;

    public function getIsActiveAttribute()
    {
        return parseBool($this->attributes['is_active']);
    }

    public function setIsActiveAttribute($value)
    {
        $value = parseBool($value);
        $this->attributes['is_active']=$value;
    }

    public function setExpirationDateAttribute($value)
    {
        if(is_string($value)){
            $value = new Carbon($value);
        }

        if($value instanceof Carbon){
            $value->setTime(0,0,0,0);
        }

        $this->attributes['expiration_date']=$value;
    }

    public function getExpirationDateAttribute()
    {
        return (new Carbon($this->attributes['expiration_date']))->format('Y-m-d');
    }

    public function products()
    {
        return $this->hasMany(Product::class,'id','product_id');
    }
}
