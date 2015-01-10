<?php

class ConstFormat
{
    const A = 'a';
    const B = <<<'ABC'
        b
ABC;
    const C = <<<DECL

DECL
    , D = <<<DECL2

DECL2
    , E = "gef";
}
