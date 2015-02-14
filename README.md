*If you are looking for a battle-tested library with a cleaner architecture and more functionality you may find your library of choice at [llaville/php-compat-info](https://github.com/llaville/php-compat-info) for now.*


# Php Version Requirement Analyser

This repository contains a library and a console application to validate your php files' version requirements.
The library makes heavy use of [the PHP-Parser library](https://github.com/nikic/PHP-Parser). Everything here is quite moving, 
if you want to use the library component yourself you may want to lock your dependencies on a specific tag. 

[![Build Status](https://travis-ci.org/suralc/pvra.svg?branch=master)](https://travis-ci.org/suralc/pvra)
[![Dependency Status](https://www.versioneye.com/user/projects/546643934de5ef5022000056/badge.svg?style=flat)](https://www.versioneye.com/user/projects/546643934de5ef5022000056)
[![Coverage Status](https://img.shields.io/coveralls/suralc/pvra.svg)](https://coveralls.io/r/suralc/pvra?branch=master)


## Index
1. [Api](#api-doc)
2. [Installation and usage of the cli app](#cli-usage)
3. [Installation and usage of the library](#lib-usage)
4. [Build the phar](#build)
5. [Todo](#todo)



## <a name="api-doc"></a>Api - Documentation

You may find the incomplete API-documentation [here](http://suralc.github.io/pvra/docs). Please be aware that at this time it is
incomplete.


## <a name="cli-usage"></a> Installation and usage of the cli app

The packed `pvra.phar` file is available as a download on the [release page](https://github.com/suralc/pvra/releases). Run
`php pvra.phar` to see a list of available commands. If you downloaded or cloned the repository itself run `php bin/pvra`
from the root of the repository. Make sure all dependencies are available (not required if you run from phar), this 
requires you to be able to run `<php> composer install --prefer-dist --no-dev` on your machine.

### Example

```php
<?php

trait Gamma
{
    public function test(callable $abc, ...$vars) {
        return $this->test()['abc'];
    }
}
```

__CLI:__ 

`php bin/pvra analyse:file ..path/to/file.php`

__OUTPUT:__

```
Required version: 5.6.0
Version 5.4.0
        Reason: Usage of the trait keyword requires PHP 5.4 in .../data/test.php:3.
        Reason: The callable typehint requires PHP 5.4 in .../data/test.php:5.
        Reason: Function dereferencing requires PHP 5.4 in .../data/test.php:6.
Version 5.6.0
        Reason: Variadic arguments require PHP 5.6 in .../data/test.php:5.
```

#### CLI - Options


**Note:** The analysers listed below can be alias using `php-5.x`.

| Name 	| Short  name 	| Description 	|
|---------------------------	|-------------	|---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------	|
| --extensive 	| -x 	|  	|
| --preventNameExpansion 	| -p 	| Prevent the expansion of names. This may improve performance. Some detections might not work. 	|
| --analyser 	| --a 	| Name of an analyser to attach. If this option is not set, all default analysers will be loaded. Possible values include: * php-5.4 * php-5.5 * php-5.6 * lib-php 	|
| --libraryDataSource 	| -l 	|  	|
| --messageFormatSourceFile 	| -m 	|  	|
| --saveFormat 	|  	| Format of the export. Only json is supported at this time. 	|
| --saveAsFile 	|  	| If this option is set the results will be saved to the given file. 	|

*Note: Classes within the src/Console directory are not part of the public API*

## <a name="lib-usage"></a>Installation and usage of the library.

Run `composer require <package-name> --prefer-dist` in the root of your project and include the composer autoloader.

Please be aware that `--prefer-dist` will reduce the download size of the loaded package by removing the `tests` directory
and other unused files.


```php
<?php

// autoloading and namespace import is assumed

$analyser = new \Pvra\StringAnalyser('<?php trait abc{}');

$analyser->attachRequirementVisitor(new Php54Features);
$analyser->attachRequirementVisitor(new Php55Features);
$analyser->attachRequirementVisitor(new Php56Features);
$analyser->attachRequirementVisitor(new LibraryChanges);

$result = $analyser->run();

echo $result->getRequiredVersion(), PHP_EOL; // 5.4.0

foreach($result as $r) {
    echo $r['msg'], 'on line ', $r['line'], PHP_EOL; 
}
```

## <a name="build"></a>Building the phar

[Box](http://box-project.org/) is required to build the phar. Run `box build` in the repository root. Box requires the code to be inside a git
repository.

## <a name="todo"></a>Todo

[See here](https://github.com/suralc/pvra/labels/todo)
