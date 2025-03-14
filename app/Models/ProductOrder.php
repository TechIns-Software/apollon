<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Thiagoprz\EloquentCompositeKey\HasCompositePrimaryKey;


/**
 * App\Models\ProductOrder
 *
 * @method static \Illuminate\Database\Eloquent\Builder|DeliveryOrder whereOrderId($value)
 */
class ProductOrder extends Model
{
    use HasFactory;
    const TABLE = 'product_order';

    protected $table=self::TABLE;

    public $incrementing = false;
    protected $primaryKey = ['order_id', 'product_id'];
    protected $fillable = ['order_id', 'product_id', 'ammount' ];


    public function order()
    {
        return $this->belongsTo(Order::class,'order_id','id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class,'product_id','id');
    }
}
