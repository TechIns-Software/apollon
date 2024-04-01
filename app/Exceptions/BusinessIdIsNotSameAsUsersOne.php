<?php

namespace App\Exceptions;

use App\Models\SaasUser;
use Exception;

class BusinessIdIsNotSameAsUsersOne extends Exception
{
    public function __construct(SaasUser $client, int $business_id)
    {
        parent::construct("User {$client->id} does not have the business_id  $business_id");
    }
}
