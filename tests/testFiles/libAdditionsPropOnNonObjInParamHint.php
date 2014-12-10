<?php

trait Gamma
{
    public function test(callable $abc, ...$vars)
    {
        return function (array $param) {
            $this->test()['abc'];
        };
    }
}
