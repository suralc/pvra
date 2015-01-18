<?php
namespace {
    $s = session_status();
    $s2 = trait_exists('abc') . hash_equals('a', 'b') . substr('abc', 1);

    foreach (get_declared_traits() as $trait) {
        $a3 = new RecursiveCallbackFilterIterator(new RecursiveDirectoryIterator('a'),
            function (JsonSerializable $serializable) {
            });
    }

    class Theta extends SessionHandler implements SessionHandlerInterface, JsonSerializable
    {
        public function jsonSerialize()
        {
        }
    }

    (new Spoofchecker())->isSuspicious('');

    interface MySessionHandler extends SessionHandlerInterface
    {
    }

    interface MySecondSessionHandler extends SessionHandlerInterface, JsonSerializable, MySessionHandler, Countable
    {
    }

    function myFunction($myParamWithoutType)
    {
    }
}
namespace MyNameSpace {
    interface JsonSerializable
    {
    }

    class MyNamespacedCoreClassNameImplementation implements JsonSerializable
    {
    }

    abstract class MyNamespacedCoreClassNameImplementation2 implements \JsonSerializable
    {
    }

    $forward = \Transliterator::FORWARD;
}
