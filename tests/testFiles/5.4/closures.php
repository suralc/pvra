<?php

$r = function (callable $abc) {
    return function (callable $def) {

    };
};

abstract class A implements ArrayAccess
{
    public function e()
    {
        return function () {
            $d = $this['bef']['dde']()['g1'];

            $this->method(function (callable $def) {
                return $this->e($def());
            });

            return $this();
        };
    }

    public function __invoke()
    {

    }
}
