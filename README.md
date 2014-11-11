# Php Version Requirement Analyser

This repository will in the feature contain a library and a console application to validate your php files' version requirements.
The library makes havy use of [the PHP-Parser library](https://github.com/nikic/PHP-Parser)


## Installation and usage of the cli app

The packed `pvra.phar` file is available as a download on the releases page (once there is a release). Run
`php pvra.phar` to see a list of available commands. If you downloaded or cloned the repository itself run `php bin/pvra`
from the root of the repository. Make sure all dependencies are available (not required if you run from phar), this 
requires you to be able to run `<php> composer install --prefer-dist --no-dev` on your machine.



## Installation and usage of the library.

Run `composer require <package-name>` in the root of your project and include the composer autoloader somewhere.

```php
    <?php

    // autoloading is assumed

    $req = new StringRequirementAnalyser('<?php trait abc {}');
    
    $req->attachRequirementAnalyser(new Analyse\LanguageFeature); // TODO: Refactor the actual code to look like this

    $result = $req->run();

    echo $result->getRequiredVersion(); // 5.4.0
    echo $result->getRequiredVersionReasoning()[0]; // Usage of the trait keyword requires php 5.4

```

## Building the phar

Box is required to build the phar. Run `box build` in the repository root. Box requires the code to be inside a git
repository.

## Working so far

* Everything that is not listed below


## Todo (more or less in order of importance)

* More tests
* Implement all language features (5.5+)
* Ability to release cyclic dependencies between Result, Analyser and NodeWalker by hand. (Or not create them in the first place)
Related to the task below.
* Refactor most of the code in src/RequirementAnalysis
* Determine when functions were introduced (Bonus: Check for default parameter changes and additional parameter usage)
* Output formatter and reworked console output
* Colored output
* Verbosity levels
* Custom exceptions (?) & better error handling