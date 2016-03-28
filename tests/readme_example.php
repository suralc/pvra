<?php

trait Gamma
{
    public function test(callable $abc, ...$vars) {
        return $this->test()['abc'];
    }
}