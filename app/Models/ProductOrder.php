<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Thiagoprz\EloquentCompositeKey\HasCompositePrimaryKey;
class ProductOrder extends Model
{
    use HasFactory;

    protected $table='product_order';

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
