<?php

namespace App\Http\Middleware;

use App\Models\Order;
use Illuminate\Database\Eloquent\Model;
class RequiresOrderId extends MissingIdBaseMiddleware
{

    protected $notFoundMsg="Η παραγγελία δεν Υπάρχει";
    protected $mergeRequestKey="order";

    protected function getModel(int $id)
    {
        return Order::find($id);
    }
}
