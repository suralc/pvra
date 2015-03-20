<?php

namespace Pvra\Foo {
    class String extends Comperator
    {
        public function String()
        {

        }

        public function compare(string $a = null, string $b = null) : int
        {
            $a = $a ?? '';
            $b = $b ?? '';
            return $a <=> $b;
        }
    }

    class_alias('Pvra\Foo\String', 'Foobar\String');
}

namespace {
    class Test
    {
        public function test()
        {

        }
    }
}
