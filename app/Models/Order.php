<?php

namespace App\Models;

use App\Exceptions\BusinessIdIsNotSameAsClientsOne;
use App\Exceptions\BusinessIdIsNotSameAsUsersOne;
use http\Exception\RuntimeException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property int $client_id
 * @property int $business_id
 * @property int $saas_user_id
 * @property string|null $description
 * @property string $status
 *
 * @property-read \App\Models\Client $client
 * @property-read \App\Models\Business $business
 * @property-read \App\Models\SaasUser $saasUser
 *
 *  Relationships:
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Order newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Order newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Order query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Order whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Order whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Order whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Order whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Order whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Order whereBusinessId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Order whereSaasUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Order whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Order whereStatus($value)
 */
class Order extends Model
{
    use HasFactory;

    protected $table="order";

    protected $fillable = [
        'client_id',
        'business_id',
        'saas_user_id',
        'status',
        'description'
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function (Order $order){
            if(
                !empty($order->saas_user_id)
                && !empty($order->business_id)
                && !empty($order->business_id)
            ){
                $user = SaasUser::find($order->saas_user_id);

                if($user->business_id != $order->business_id){
                    throw new BusinessIdIsNotSameAsUsersOne($user,$order->business_id);
                }

                $client = Client::find($order->client_id);
                if($client->business_id != $order->business_id){
                    throw new BusinessIdIsNotSameAsClientsOne($client,$order->business_id);
                }
            }
        });
    }
}
