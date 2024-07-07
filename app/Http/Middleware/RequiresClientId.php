<?php

namespace App\Http\Middleware;

use App\Models\Client;

class RequiresClientId extends MissingIdBaseMiddleware
{
    protected $notFoundMsg="O πελάτης δεν υπάρχει";
    protected $mergeRequestKey="client";

    protected function getModel(int $id)
    {
        return Client::find($id);
    }
}
