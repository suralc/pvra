<?php

$a = true ? true : false;
$b = $a ?: false;
$c = $a ?: $b ? false : $a === $b ? $a : $b === $a ?: 'no';
