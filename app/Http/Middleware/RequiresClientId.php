<?php

namespace App\Http\Middleware;

use App\Models\Client;

class RequiresClientId extends MissingIdBaseMiddleware
{
    protected $notFoundMsg="Η παραγγελία δεν Υπάρχει";
    protected $mergeRequestKey="client";

    protected function getModel(int $id)
    {
        return Client::find($id);
    }
}
