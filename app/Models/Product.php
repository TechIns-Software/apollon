<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    const TABLE = 'product';
    protected $table=self::TABLE;

    public function productOrder()
    {
        return $this->hasMany(ProductOrder::class,'order_id','id');
    }
}
