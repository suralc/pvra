#!/usr/bin/env  php
<?php
/**
 * json-gen.php
 *
 * MIT LICENSE
 *
 * LICENSE: This source file is subject to the MIT license.
 * A copy of the licenses text was distributed alongside this
 * file (usually the repository or package root). The text can also
 * be obtained on one of the following sources:
 * * http://opensource.org/licenses/MIT
 * * https://github.com/suralc/pvra/blob/master/LICENSE
 *
 * @author     suralc <thesurwaveing@gmail.com>
 * @license    http://opensource.org/licenses/MIT  MIT
 */


if ((!$loader = includeIfExists(__DIR__ . '/../vendor/autoload.php'))
    && (!$loader = includeIfExists(__DIR__ . '/../../../autoload.php'))
) {
    if (!file_exists(__DIR__ . '/../src/Result/Reason.php')) {
        echo 'Pvra needs to be able to locate composers autoload.php or the Pvra\Result\Reason class.', PHP_EOL;
        die(2);
    }
    require_once __DIR__ . '/../src/Result/Reason.php';
}

$shortOpts = '';
$longOpts = [
    'path:',
    'ignore:',
    'help',
    'pretty',
    'force-regen'
];

$options = getopt($shortOpts, $longOpts);

if (isset($options['help']) || (isset($options['path']) && empty($options['path']))) {
    displayHelp();
    exit(0);
}

if (isset($options['path'])) {
    $path = $options['path'];
} else {
    $path = getcwd();
}

if (!is_dir($path) || !is_readable($path)) {
    echo $path . ' is not a directory or is not readable.';
    exit(2);
}

$ignore = [];
if (!empty($options['ignore'])) {
    $ignore = explode(',', $options['ignore']);
    array_walk($ignore, function (&$val) {
        $val = trim($val);
    });
}

$jsonFlags = 0;
if (isset($options['pretty'])) {
    $jsonFlags |= JSON_PRETTY_PRINT;
}

$directory = new RecursiveDirectoryIterator($path);
$iterator = new RecursiveIteratorIterator($directory);
$filesHandled = 0;

/** @var SplFileInfo $file */
foreach ($iterator as $file) {
    if (!$file->isFile() || $file->getRealPath() === __FILE__ || isFileToBeIgnored($file, $ignore)) {
        continue;
    }
    $filesHandled++;
    $jsonEq = $file->getPath() . DIRECTORY_SEPARATOR . $file->getBasename('.php') . '.json';
    if (empty($options['force-regen']) && file_exists($jsonEq)
        && filemtime($jsonEq) > $file->getMTime()
    ) {
        echo '"', $file, '" already has a recent json equivalent.', PHP_EOL;
        continue;
    }

    $values = include $file->getRealPath();
    file_put_contents($jsonEq, json_encode($values, $jsonFlags));

    echo $file, PHP_EOL;
}

echo 'Files found: ', iterator_count($iterator), PHP_EOL;
echo 'Files handled: ', $filesHandled, PHP_EOL;

function isFileToBeIgnored(SplFileInfo $file, array $userIgnoreList)
{
    if ($file->getExtension() !== 'php') {
        return true;
    }
    $pathSegments = explode(DIRECTORY_SEPARATOR, $file->getPath());
    if (array_intersect($pathSegments, $userIgnoreList) !== []) {
        return true;
    }

    return false;
}

function displayHelp()
{
    ?>
    <?= basename(__FILE__, '.php'); ?> tool for pvra v1.0.

    Usage:
    --help       Display this message.
    --path       Base path to scan for generation. Value required if passed
    --ignore     Directories to ignore. Value required if passed.
    --force-regen   Regenerate files even if files are current.
<?php
}

function includeIfExists($file)
{
    if (file_exists($file)) {
        return include $file;
    }

    return null;
}
