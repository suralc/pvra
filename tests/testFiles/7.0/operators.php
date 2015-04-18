<?php

// examples taken from: https://wiki.php.net/rfc/combined-comparison-operator and https://wiki.php.net/rfc/isset_ternary
function order_func($a, $b)
{
    return $a <=> $b;
}

function order_func2($a, $b)
{
    return ($a->$x <=> $b->x)
                ?: ($a->y <=> $b->y)
                     ?: ($a->foo <=> $b->foo);
}

usort($data, function ($left, $right) {
    return $left[1] <=> $right[1];
});

$username = $_GET['user'] ?? 'nobody';
var_dump($x ?? $y ?? $z);
