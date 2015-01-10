<?php

namespace Pvra\tests\testFiles {

    use Pvra\tests\BaseNodeWalkerTestCase;

    class MyWalkerTestDemo extends BaseNodeWalkerTestCase
    {
        public $abc;
        private static $def;
        protected static $delta;
        const DOC_CONST = <<<HELLO_DOC
Hello from doc constant in class.
HELLO_DOC;


        public static function getMockedWalker()
        {
            label1:
            static::$def = 12;
            $var = __NAMESPACE__ . '\MyWalkerTestDemo';
            $var::$delta;
            if (static::$def !== 12) {
                goto label1;
            }
            static::$delta = <<<'DELTACONTENT'
Hello World from $delta.
DELTACONTENT;
        }

        public static function __callStatic()
        {
            return 'Hello from __callStatic';
        }

        public function __invoke()
        {
            return function() {
                return 'Hello from closure';
            };
        }
    }
}

namespace {
    use Pvra\tests\testFiles\MyWalkerTestDemo;

    const CONST_OUTSIDE_CLASS = 'Abc';

    MyWalkerTestDemo::__callStatic();
    $walker = new MyWalkerTestDemo();
    $walker->__InVoKe();
    $runningWalker = $walker ?: null;
    $anotherTernary = $runningWalker instanceof MyWalkerTestDemo ? 'a' : 'b';
}
