<?php


use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

if(!function_exists('parseBool')){
    function parseBool($value)
    {
        if(is_bool($value)){
            return $value;
        }

        if(is_string($value)){
            $value=strtolower(trim($value));
        }

        if(is_numeric($value)){
            $value = (int)$value;
        }
        return in_array($value,[1,true,'on','yes','true'],true);
    }
}


if(!function_exists('validateBooleableValue')) {

    function validateBooleableValue($value)
    {
        if (is_bool($value)) {
            return true;
        }

        if (is_string($value)) {
            $value = strtolower($value);
        }

        if (is_numeric($value)) {
            $value = (int)$value;
        }

        return in_array($value, [0, 1, 'true', 'false', 'yes', 'no', 'on', 'off']);
    }
}

if(!function_exists('cast_greek_float')){
    function cast_greek_float($value):float
    {
        if(is_string($value)){
            $value = str_replace('.',"",$value);
            $value = str_replace(',',".",$value);
        }

        return floatval($value);
    }
}

if(!function_exists('validatePaginationAndSortening')){
    function validatePaginationAndSortening(array $requestData,array $extraValidationRules=[],array $extraValidationMessages=[])
    {
        $errors = [
            ...$extraValidationMessages,
            'page'=>"Page must have positive value",
            'limit'=>"Limit must have positive value",
        ];

        $validator = Validator::make($requestData, [
            ...$extraValidationRules,
            'page'=>"sometimes|integer|min:1",
            "limit"=>"sometimes|integer|min:1",
            'order_by'=>'required_with:ordering|string',
            'ordering'=>'required_with:order_by|string|in:ASC,DESC,asc,desc'
        ],$errors);

        if($validator->fails()){
            throw new ValidationException($validator);
        }
    }
}

if(!function_exists('uniqueWithLargestKey')){
    function uniqueWithLargestKey($array) {
        // Reverse the array to prioritize items with larger keys
        $reversedArray = array_reverse($array, true);
        // Remove duplicates while keeping the first occurrence (which is the largest key due to reversal)
        $uniqueArray = array_unique($reversedArray, SORT_REGULAR);
        // Reverse the array back to its original order
        return array_reverse($uniqueArray, true);
    }

}
