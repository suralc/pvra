<?php

$a =& new stdClass();
$b = new stdClass();
$x &= new stdClass(); // bitwise, not by mistake
$c = 'stdclass';
$e = &new $c();
$b &= $c; // see above
$d =& $c;
