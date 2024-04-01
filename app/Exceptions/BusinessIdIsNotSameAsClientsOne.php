<?php

namespace App\Exceptions;

use App\Models\SaasUser;
use Exception;

class BusinessIdIsNotSameAsClientsOne extends Exception
{
    public function __construct(Client $client, int $business_id)
    {
        parent::construct("Client {$client->id} does not have the business_id  $business_id");
    }
}
