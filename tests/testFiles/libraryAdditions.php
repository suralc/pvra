<?php

$s = session_status();
$s2 = trait_exists('abc') . hash_equals('a', 'b');

foreach (get_declared_traits() as $trait) {
    $a3 = new RecursiveCallbackFilterIterator(new RecursiveDirectoryIterator('a'),
        function (JsonSerializable $serializable) {
        });
}

class Theta extends SessionHandler implements SessionHandlerInterface, JsonSerializable
{
    public function jsonSerialize()
    {
        // TODO: Implement jsonSerialize() method.
    }
}

(new Spoofchecker())->isSuspicious('');

interface MySessionHandler extends SessionHandlerInterface
{
}

interface MySecondSessionHandler extends SessionHandlerInterface, JsonSerializable, MySessionHandler
{
}
