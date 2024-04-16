<?php

class Myclass
{
    const CONSTANT='value';

    function printConstant(): void
    {
        echo self::CONSTANT.PHP_EOL;
    }
}

class AnotherClass extends Myclass
{
    const CONSTANT='value2';
}

$a = new AnotherClass();
$myclass = new Myclass();

$myclass->printConstant();
$a->printConstant();
