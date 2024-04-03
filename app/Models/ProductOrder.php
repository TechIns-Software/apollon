<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Thiagoprz\EloquentCompositeKey\HasCompositePrimaryKey;
class ProductOrder extends Model
{
    use HasFactory;

    protected $table='product_order';

    public $incrementing = false;
    protected $primaryKey = ['order_id', 'business_id'];
    protected $fillable = ['order_id', 'business_id', 'ammount' ];


}
