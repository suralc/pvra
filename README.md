*If you are looking for a battle-tested library with a possibly cleaner architecture and similar features you may find your app of choice at [llaville/php-compat-info](https://github.com/llaville/php-compat-info) for now.*


# Php Version Requirement Analyser

This repository contains a library and a console application to validate your php files' version requirements.
The library makes heavy use of [the PHP-Parser library](https://github.com/nikic/PHP-Parser). The api is not yet final, 
if you want to use the library component yourself you may want to lock your dependencies on a specific tag. 

[![Build Status](https://travis-ci.org/suralc/pvra.svg?branch=master)](https://travis-ci.org/suralc/pvra)
[![Dependency Status](https://www.versioneye.com/user/projects/546643934de5ef5022000056/badge.svg?style=flat)](https://www.versioneye.com/user/projects/546643934de5ef5022000056)
[![Code Coverage](https://scrutinizer-ci.com/g/suralc/pvra/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/suralc/pvra/?branch=master)

## Index
1. [Installation and usage of the cli app](#cli-usage)
2. [Installation and usage of the library](#lib-usage)
3. [Build the phar](#build)
4. [Library Api Docs](#api-doc)
5. [Todo](#todo)


## <a name="cli-usage"></a> Installation and usage of the cli app

The packed `pvra.phar` file is available as a download on the [release page](https://github.com/suralc/pvra/releases). Run
`php pvra.phar` to see a list of available commands. If you downloaded or cloned the repository itself run `php bin/pvra`
from the root of the repository. Make sure all dependencies are available (not required if you run from phar), this 
requires you to be able to run `<php> composer install --prefer-dist --no-dev` on your machine.

Pvra can be installed globally using the `composer global require suralc/pvra:dev-master` command. Be sure to add the
`$COMPOSER_HOME/vendor/bin` directory to your `$PATH`. [Read more about composer global](https://getcomposer.org/doc/03-cli.md#global)


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

`php pvra.phar analyse:file ./tests/readme_example.php --preferRelativePaths`

__OUTPUT:__

```
Running analysis for "./tests/readme_example.php"
Required version: 5.6.0
+---------+-----------------------------------------------+------+
| Version | Message                                       | Line |
+---------+-----------------------------------------------+------+
| 5.6.0   | Variadic arguments require PHP 5.6.0          | 5    |
| 5.4.0   | Usage of the trait keyword requires PHP 5.4.0 | 3    |
| 5.4.0   | The callable typehint requires PHP 5.4.0      | 5    |
| 5.4.0   | Function dereferencing requires PHP 5.4.0     | 6    |
+---------+-----------------------------------------------+------+
```

#### CLI - Options

| Name 	| Short  name 	| Description 	|
|---------------------------	|-------------	|---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------	|
| --preventNameExpansion 	| -p 	| Prevent the expansion of names. This may improve performance. Some detections might not work. 	|
| --preferRelativePaths   | -s  | Only show relative paths in the console output. Fall back to absolute paths if no simple relative path can be built |
| --analyser 	| -a 	| Name of an analyser to attach. If this option is not set, all default analysers will be loaded. Possible values are: php-5.4, php-5.5, php-5.6, php-7.0, lib-php |
| --libraryDataSource 	| -l 	| Path to a file containing library information. Defaults to the file shipped with the library/phar.	|
| --messageFormatSourceFile 	| -m 	| Path to a file containing message templates. Defaults to the file shipped with the library/phar.	|
| --saveFormat 	|  	| Format of the export. Only json is supported at this time. 	|
| --saveAsFile 	|  	| If this option is set the results will be saved to the file specified as value. 	|

*Note: Classes within the src/Console directory are not part of the public API*

## <a name="lib-usage"></a>Installation and usage of the library.

Run `composer require suralc/pvra --prefer-dist` in the root of your project and include the composer autoloader.

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
    echo $r['msg'], PHP_EOL; // Outputs: Usage of the trait keyword requires PHP 5.4.0 in <string>:1
}
```

## <a name="build"></a>Building the phar

[Box](http://box-project.org/) is required to build the phar. Run `box build` in the repository root. Box requires the code to be inside a git
repository.



## <a name="api-doc"></a>Api - Documentation

You may find the incomplete API-documentation [here](http://suralc.github.io/pvra/docs). Please be aware that at this time it is
incomplete.




## <a name="todo"></a>Todo

[See here](https://github.com/suralc/pvra/labels/todo)
