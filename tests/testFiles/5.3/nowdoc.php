<?php

$a = <<<HELLO
    <<<'Hello'
    World
HELLO;

$b = <<<'INPUT'
empty
INPUT;

strpos(<<<ARG
    haystack
ARG
    , <<<'NEEDLE'
    needle
NEEDLE
);
