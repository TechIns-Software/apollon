<?php


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
