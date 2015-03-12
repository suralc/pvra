<?php

const A = 5;
const B = 5 + 5;
const C = A + 5;
const D = A + A;

class ConstTestExample
{
    const A = 10;
    const B = 10 + 10;
    const D = self::A + 10;
    const E = self::A + self::A;
    const F = ConstTestExample::B;
    const G = \D;
}
