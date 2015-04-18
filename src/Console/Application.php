<?php
/**
 * Application.php
 *
 * MIT LICENSE
 *
 * LICENSE: This source file is subject to the MIT license.
 * A copy of the licenses text was distributed alongside this
 * file (usually the repository or package root). The text can also
 * be obtained through one of the following sources:
 * * http://opensource.org/licenses/MIT
 * * https://github.com/suralc/pvra/blob/master/LICENSE
 *
 * @author     suralc <suralc.github@gmail.com>
 * @license    http://opensource.org/licenses/MIT  MIT
 */
namespace Pvra\Console;


use Symfony\Component\Console\Application as BaseApplication;

/**
 * Class Application
 *
 * @package Pvra\Console
 */
class Application extends BaseApplication
{
    const APPLICATION_DEFAULT_VERSION = '0.2.0-dev';

    /**
     * @inheritdoc
     */
    public function __construct($name = 'UNKNOWN', $version = '@package_version@')
    {
        if ($version === '@package' . '_version@') {
            $version = static::APPLICATION_DEFAULT_VERSION;
        }

        parent::__construct($name, $version);
    }
}
