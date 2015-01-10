<?php

$f = function(
    callable ...$abc // line 4
) {};

function abc(
    ...$param // line 8
) {}

class variadicMethodAndStaticMethod
{
    public function abc(...$var) // line 13
    {
        return function(...$args) { // line 15
            return $this->abc($args);
        };
    }

    public static function def(...$vars) // line 20
    {

    }
}
