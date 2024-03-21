<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @param int id
 * @param string name
 */
class Business extends Model
{
    use HasFactory;

    protected $table="business";


}
