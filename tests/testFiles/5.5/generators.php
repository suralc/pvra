<?php

// https://wiki.php.net/rfc/generators#
function bonus()
{
    yield $key => $value; // line 6
    yield $value; // line 7
    yield; // line 8

    $data = (yield $key => $value); // line 10
    $data = (yield $value); // line 11

    $data = yield; // line 13
    call(yield
    $value); // yield on line 14

    if (yield $value) {} // line 17

    echo T_YIELD;
    echo 'yield';
}
