<?php

namespace Pvra\tests\testFiles;

class LsbBase
{
    const DEV = 'abc';
    protected static $abc;

    protected static function protMethod()
    {

    }

    public static function method()
    {
        self::protMethod();
        static::protMethod();
        $abc = static::DEV;
        static::$abc = $abc;
        $a = 'Lsb' . 'Base';
        $a::$abc;
        //def()::$abc; // valid for php 7, not supported by php parser, yet.
        $d = 'abc';
        self::${'abc'};
        static::$$d;
    }
}
