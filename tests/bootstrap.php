<?php


$loader = require __DIR__ . '/../vendor/autoload.php';

define('TEST_FILE_ROOT', __DIR__ . '/testFiles/');

/** @var \Composer\Autoload\ClassLoader() $loader */
$loader->addPsr4('Pvra\\tests\\', __DIR__);
