<?php

class Test
{
    public function __invoke()
    {

    }

    public static function __callStatic()
    {

    }
}

$t = new Test();
$t->__invoke();
Test::__callStatic();
