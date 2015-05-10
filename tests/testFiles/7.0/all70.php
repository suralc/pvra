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

    class YieldFrom
    {
        public function gen1()
        {
            for ($i = 0; $i < 10; $i++) {
                yield $i;
            }
        }

        public function gen2()
        {
            yield from $this->get1();
        }

        public function gen3()
        {
            yield "from";
        }

        public function get4()
        {
            yield from from();
        }
    }
}
