<?php
namespace App\Rules;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\DB;

class UniqueEmailForBusiness implements ValidationRule
{
    protected int $businessId;

    /**
    * Create a new rule instance.
    *
    * @param  int  $businessId
    * @return void
    */
    public function __construct(int $businessId)
    {
        $this->businessId = $businessId;
    }

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, \Closure $fail): void
    {
        $exists = DB::table('saas_user')
        ->where('email', $value)
        ->where('business_id', $this->businessId)
        ->exists();

        if($exists){
            $fail('Ο χρήστης με το email αυτό ήδη υπάρχει');
        }
    }
}
