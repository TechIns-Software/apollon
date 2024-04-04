<?php

namespace App\Http\Validation;

use App\Models\Product;

/**
 * @author Desyllas Dimitrios
 * Instead of having rules I use this class to provide methods that will be used upon validation closures.
 *
 * I use this approach because I need both validation AND data fetching.
 * Also typical rules do not allow me to both validate and aggregate and return any data retrieved from the DB
 */
class OrderValidationClosure
{
    /**
     * Validates the proced that user gives fro the Order.
     * The input I validate has thwe following form:
     *
     * {
     *     ^product_id^:^ammount^
     * }
     *
     *
     *
     * @param string $attribute The parameter name
     * @param mixed $value Ther input value
     * @param \Closure $fail Callback ifd validation fails
     * @param int $business_id The business that product should have
     * @param Product|null $product The found product
     * @return bool True if input is valid according to the implemented logic
     */
    public static function validateOrderItems(string $attribute, mixed $value, \Closure $fail,int $business_id,?Product &$product=null): bool
    {
        // The validator gets the
        $productId = (int)str_replace('items.',"",$attribute);
        $product = Product::find($productId);
        if(empty($product)){
            $fail("Το προϊόν δεν υπάρχει");
            return false;
        }
        if($product->business_id != $business_id){
            $fail("Αδυναμία Επεξεργασίας");
            return false;
        }

        return true;

    }
}
