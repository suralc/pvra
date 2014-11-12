<?php

namespace Pvra\Console;


use Symfony\Component\Console\Application as BaseApplication;

class Application extends BaseApplication
{
    const APPLICATION_DEFAULT_VERSION = '0.1.0-dev';

    /**
     * @inheritdoc
     */
    public function __construct($name = 'UNKNOWN', $version = 'UNKNOWN')
    {
        if ($version === '@package_version') {
            $version = static::APPLICATION_DEFAULT_VERSION;
        }

        parent::__construct($name, $version);
    }
}