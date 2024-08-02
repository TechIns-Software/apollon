<?php
function uniqueWithLargestKey($array) {
    // Reverse the array to prioritize items with larger keys
    $reversedArray = array_reverse($array, true);

    // Remove duplicates while keeping the first occurrence (which is the largest key due to reversal)
    $uniqueArray = array_unique($reversedArray, SORT_REGULAR);

    // Reverse the array back to its original order
    return array_reverse($uniqueArray, true);
}

// Example usage
$array = [
    0 => 'apple',
    1 => 'banana',
    2 => 'apple',
    3 => 'orange',
    4 => 'banana'
];

$result = uniqueWithLargestKey($array);
print_r($result);
?>
