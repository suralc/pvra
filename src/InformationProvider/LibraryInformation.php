<?php
/**
 * LibraryInformation.php
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
 * @author     suralc <suralc.github@gmail.com>
 * @license    http://opensource.org/licenses/MIT  MIT
 */

namespace Pvra\InformationProvider;

/**
 * Class LibraryInformation
 *
 * @package Pvra\InformationProvider
 */
class LibraryInformation implements LibraryInformationInterface
{
    /**
     * @var array
     */
    private $additions = [];
    /**
     * @var array
     */
    private $deprecations = [];
    /**
     * @var array
     */
    private $removals = [];

    /**
     * Create a new instance and load default information
     *
     * This methods loads the changes.php file distributed with the library or phar.     *
     *
     * @return static
     */
    public static function createWithDefaults()
    {
        $source = __DIR__ . '/../../data/library/php/changes.php';

        return static::createFromFile($source);
    }

    /**
     * Create a new instance based on a given file path
     *
     * The filepath given to this method has to represent a php file returning
     * an array with a valid structure.
     *
     * @param string $source Valid path to data source
     * @return static
     */
    public static function createFromFile($source)
    {
        if (!is_file($source) || !is_readable($source)) {
            throw new \InvalidArgumentException(sprintf('The file "%s" does not exist or is not readable',
                $source));
        }

        return new static(include $source);
    }


    /**
     * Create a new instance using the supplied data
     *
     * @param array $data Array representation of data to represent
     */
    public function __construct(array $data = [])
    {
        if (isset($data['additions'])) {
            $this->additions = $data['additions'];
        }
        if (isset($data['deprecations'])) {
            $this->deprecations = $data['deprecations'];
        }
        if (isset($data['removals'])) {
            $this->removals = $data['removals'];
        }

        $baseArray = ['class' => [], 'function' => [], 'constant' => []];
        $this->additions += $baseArray;
        $this->deprecations += $baseArray;
        $this->removals += $baseArray;
    }

    /**
     * @inheritdoc
     */
    public function toArray()
    {
        return [
            'additions' => $this->additions,
            'deprecations' => $this->deprecations,
            'removals' => $this->removals,
        ];
    }

    /**
     * @inheritdoc
     */
    public function mergeWith(LibraryInformationInterface $info)
    {
        $newInfo = $info->toArray();
        $this->additions = array_replace_recursive($this->additions, $newInfo['additions']);
        $this->deprecations = array_replace_recursive($this->deprecations, $newInfo['deprecations']);
        $this->removals = array_replace_recursive($this->removals, $newInfo['removals']);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getFunctionInfo($name)
    {
        return $this->getInfo($name, 'function');
    }

    /**
     * @inheritdoc
     */
    public function getClassInfo($name)
    {
        return $this->getInfo($name, 'class');
    }

    /**
     * @inheritdoc
     */
    public function getConstantInfo($name)
    {
        return $this->getInfo($name, 'constant');
    }

    /**
     * @param string $name Name of the item
     * @param string $type Type of the item (function, class or constant
     * @return array An array in the format:
     * <code>
     * ['addition' => Version|null, 'deprecation' => Version|null, 'removal' => Version|null]
     * </code>
     */
    private function getInfo($name, $type)
    {
        $name = ltrim($name, '\\');
        return [
            'addition' => isset($this->additions[ $type ][ $name ]) ? $this->additions[ $type ][ $name ] : null,
            'deprecation' => isset($this->deprecations[ $type ][ $name ]) ? $this->deprecations[ $type ][ $name ] : null,
            'removal' => isset($this->removals[ $type ][ $name ]) ? $this->removals[ $type ][ $name ] : null,
        ];
    }
}
