<?php

trait Gamma
{
    public function test(callable $abc, ...$vars)
    {
        return function () {
            $this->test()['abc'];
        };
    }
}

trait Delta
{
    public function test2()
    {
        return __TRAIT__;
    }

    public function test3()
    {
        return 'abc' . __TRAIT__ . 'def';
    }
}

trait Epsilon
{
    private $prop;

    public function test2()
    {
        $r = function () {
            $this->prop;
        };
    }
}

class Omega
{
    use Gamma;
    use Delta, Epsilon {
        Delta::test2 insteadof Epsilon;
        Delta::test3 as public overtune;
    }

    public static function test5()
    {
        $dito = 'me too';
        $d = function () use ($dito) {

        };
    }
}

Omega::test5()['hello world'];

(new Omega())->overtune();
(new $abc)->test2();

$a = 12;
$b = 0b01010101001010001010;

$c = array(
    'a' => [
        'd' => 'e',
        'f' => [
            'g' => array(),
        ],
    ]
);

callMyFunction([
    'a',
], array());

$callableWithArrayTypehint = function (array $arg1 = []) {
    return function (array $arg2 = array()) {
    };
};


$d = 0x4e2;
$e = 0075;
