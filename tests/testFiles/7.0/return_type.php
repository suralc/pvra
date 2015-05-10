<?php

function foo() {}
function bar() : baz {}
function barBaz() : callable {
    return function() : callable {
        return function() {
            return false;
        };
    };
}

class Fish {
    public function getCat() : Cat {}
    public function ignoreCats(){}
    function foo(){}
    public function getCallable() : callable {
        return function() : bool {
            return false;
        };
    }
}

$t = function(){};
$g = function() : Fish {};

