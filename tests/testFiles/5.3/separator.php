<?php
// detection should not trigger on namespace and use statements
namespace A\B\C\D\E\H;

use D\E\F\G as Y;
use Symfony\Component\Console\Input\ArgvInput;

$a = new A\B\D\E\F;
$b = A\B\D\E\F::class;
$c = E\F\G::$abd;
$e = \E\F::getSomeVar();
E\F::$abd = 'abc';
throw new \Exception();
$d = new ArgvInput(); // should not trigger detection. (Already handled by use message);
