# Php Version Requirement Analyser

This repository will contain a library and a console application to validate your php files' version requirements.
The library makes heavy use of [the PHP-Parser library](https://github.com/nikic/PHP-Parser)


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
$req->attachRequirementAnalyser(new LanguageFeatureAnalyser);

$result = $req->run();

echo $result->getRequiredVersion(); // 5.4.0
echo $result->getRequiredVersionReasoning()[0]; // Usage of the trait keyword requires php 5.4

```

## Building the phar

[Box](http://box-project.org/) is required to build the phar. Run `box build` in the repository root. Box requires the code to be inside a git
repository.

## Working so far

* Everything that is not listed below


## Todo (more or less in order of importance)

* More tests
* Implement all language features (5.5+)
* Missing 5.4: Detection of 0b001001101 style number, Class::{expr}(), $this in closure
* Refactor most of the code in src/RequirementAnalysis and split Pvra\PhpParser\AnalysingNodeWalkers\LanguageFeatureAnalyser up.
* Determine when functions were introduced (Bonus: Check for default parameter changes and additional parameter usage)
* Output formatter and reworked console output
* Colored output
* Verbosity levels
* Custom exceptions (?) & better error handling