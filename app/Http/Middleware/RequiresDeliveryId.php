<?php

namespace App\Http\Middleware;

use App\Models\Client;
use App\Models\Delivery;

class RequiresDeliveryId extends MissingIdBaseMiddleware
{
    protected $notFoundMsg="Το δρομόλογιο δεν υπάρχει.";
    protected $mergeRequestKey="delivery";

    protected function getModel(int $id)
    {
        return Delivery::find($id);
    }
}
