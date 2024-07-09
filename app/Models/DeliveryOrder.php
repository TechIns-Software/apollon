<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


/**
 * App\Models\DeliveryOrder
 *
 * @property int $id
 * @property int $order_id
 * @property int $delivery_id
 * @property int $delivery_sequence
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|DeliveryOrder newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DeliveryOrder newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DeliveryOrder query()
 * @method static \Illuminate\Database\Eloquent\Builder|DeliveryOrder whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DeliveryOrder whereDeliveryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DeliveryOrder whereDeliverySequence($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DeliveryOrder whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DeliveryOrder whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DeliveryOrder whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class DeliveryOrder extends Model
{
    use HasFactory;

    const TABLE = 'delivery_order';

    protected $table=self::TABLE;

    protected $fillable = ['order_id','delivery_id','delivery_sequence'];

    public function order()
    {
        return $this->hasOne(Order::class,'id','order_id');
    }

    public function delivery()
    {
        return $this->hasOne(Delivery::class,'id','delivery_id');
    }
}
