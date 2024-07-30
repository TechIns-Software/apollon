<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\Models\Delivery
 *
 * @property int $id
 * @property int $business_id
 * @property int $driver_id
 * @property string $name
 *
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $delivery_date
 *
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\DeliveryOrder[] $deliveryOrder
 * @property-read int|null $delivery_order_count
 * @property-read string $pdf_url
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Delivery newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Delivery newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Delivery query()
 * @method static \Illuminate\Database\Eloquent\Builder|Delivery whereBusinessId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Delivery whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Delivery whereDeliveryDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Delivery whereDriverId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Delivery whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Delivery whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Delivery whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class Delivery extends Model
{
    use HasFactory,SoftDeletes;

    const TABLE="delivery";
    protected $table=self::TABLE;

    protected $fillable=[
        'driver_name',
        'delivery_date',
        'name',
        'driver_id',
        'business_id'
    ];
    protected $appends = ['pdf_url'];

    public function deliveryOrder()
    {
        return $this->hasMany(DeliveryOrder::class,'delivery_id','id')->orderBy('delivery_sequence');;
    }

    public function driver()
    {
        return $this->hasOne(Driver::class,'id','driver_id');
    }

    public function getPdfUrlAttribute()
    {
        return route('delivery_pdf',['id'=>$this->id]);
    }
}
