*If you are looking for a battle-tested library with a cleaner architecture and more functionality you may find your library of choice at [llaville/php-compat-info](https://github.com/llaville/php-compat-info) for now.*


# Php Version Requirement Analyser

This repository contains a library and a console application to validate your php files' version requirements.
The library makes heavy use of [the PHP-Parser library](https://github.com/nikic/PHP-Parser)

[![Build Status](https://travis-ci.org/suralc/pvra.svg?branch=master)](https://travis-ci.org/suralc/pvra)
[![Dependency Status](https://www.versioneye.com/user/projects/546643934de5ef5022000056/badge.svg?style=flat)](https://www.versioneye.com/user/projects/546643934de5ef5022000056)
[![Coverage Status](https://img.shields.io/coveralls/suralc/pvra.svg)](https://coveralls.io/r/suralc/pvra?branch=master)



## Api - Documentation

You may find the incomplete API-documentation [here](http://suralc.github.io/pvra/docs). Please be aware that at this time it is
uncomplete.


## Installation and usage of the cli app

The packed `pvra.phar` file is available as a download on the releases page (once there is a release). Run
`php pvra.phar` to see a list of available commands. If you downloaded or cloned the repository itself run `php bin/pvra`
from the root of the repository. Make sure all dependencies are available (not required if you run from phar), this 
requires you to be able to run `<php> composer install --prefer-dist --no-dev` on your machine.

### Example

```php
<?php

// data/test.php
// code does not make sense, it's still a nice example

trait Gamma
{
    public function test(callable $abc, ...$vars) {
        return $this->test()['abc'];
    }
}
```

__CLI:__ 

`php bin/pvra analyse:file -f data/test.php`

__OUTPUT:__

```
Required version: 5.6.0
Version 5.4.0
        Reason: Usage of the trait keyword requires PHP 5.4 in .../data/test.php:3.
        Reason: The callable typehint requires php 5.4 in .../data/test.php:5.
        Reason: Function dereferencing requires php 5.4 in .../data/test.php:6.
Version 5.6.0
        Reason: Variadic arguments require php 5.6 in .../data/test.php:5.
```




## Installation and usage of the library.

Run `composer require <package-name>` in the root of your project and include the composer autoloader somewhere.

```php
<?php

// autoloading and namespace import is assumed

$req = new StringRequirementAnalyser('<?php trait abc{}');

$req->attachRequirementVisitor(new Php54LanguageFeatureNodeWalker);
$req->attachRequirementVisitor(new Php55LanguageFeatureNodeWalker);
$req->attachRequirementVisitor(new Php56LanguageFeatureNodeWalker);
$req->attachRequirementVisitor(new LibraryAdditionsNodeWalker);

$result = $req->run();

echo $result->getRequiredVersion(), PHP_EOL; // 5.4.0

foreach($result as $r) {
    echo $r['msg'], 'on line ', $r['line'], PHP_EOL; 
}
```

## Building the phar

[Box](http://box-project.org/) is required to build the phar. Run `box build` in the repository root. Box requires the code to be inside a git
repository.

## Todo

[See here](https://github.com/suralc/pvra/labels/todo)
