<?php


$loader = require __DIR__ . '/../vendor/autoload.php';

define('TEST_FILE_ROOT', __DIR__ . '/testFiles/');
define('COMMAND_FORMAT_FILE_ROOT', TEST_FILE_ROOT . '/commandFormatFiles/');

/** @var \Composer\Autoload\ClassLoader() $loader */
$loader->addPsr4('Pvra\\tests\\', __DIR__);
