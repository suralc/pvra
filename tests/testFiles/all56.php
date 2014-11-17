<?php

namespace Pvra\tests\testFiles;

use const CURL_HTTP_VERSION_1_0;
use function \strlen;

function abc(...$args)
{
    return def(...$args);
}

class Asterix
{
    const ONE = 1;
    const TWO = self::ONE + self::ONE;
    const FOUR = 2 + 2;

    public function fightAgainstRome($year, ...$events)
    {
        return function (...$troops) use ($events) {

        };
    }

    public static function randomResult(...$args) {
        $r = 5 ** 5;
        $r = 4 ** $r;
        $r = $r ** $r;
        $r **= $r;
        $r **= ((((12 * 5 / intval('88')) % 6) + 87 ** 298) / 2);
    }
}
$args = [5,4,3,7,8,9];

Asterix::randomResult(...$args);
(new Asterix())->fightAgainstRome(-50, ...$args);
