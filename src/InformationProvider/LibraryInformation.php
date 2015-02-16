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
 * @author     suralc <thesurwaveing@gmail.com>
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
     * Get the array representation of the instance
     * @return array Array representation of the data stored in this object
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
     * Merge another instance into this instance
     * @param \Pvra\InformationProvider\LibraryInformationInterface $info
     * @return $this
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
     * @param string $name Function name
     * @return array
     */
    public function getFunctionInfo($name)
    {
        $name = ltrim($name, '\\');
        return [
            'addition' => isset($this->additions['function'][ $name ]) ? $this->additions['function'][ $name ] : null,
            'deprecation' => isset($this->deprecations['function'][ $name ]) ? $this->deprecations['function'][ $name ] : null,
            'removal' => isset($this->removals['function'][ $name ]) ? $this->removals['function'][ $name ] : null,
        ];
    }

    /**
     * @param string $name Class name
     * @return array
     */
    public function getClassInfo($name)
    {
        $name = ltrim($name, '\\');
        return [
            'addition' => isset($this->additions['class'][ $name ]) ? $this->additions['class'][ $name ] : null,
            'deprecation' => isset($this->deprecations['class'][ $name ]) ? $this->deprecations['class'][ $name ] : null,
            'removal' => isset($this->removals['class'][ $name ]) ? $this->removals['class'][ $name ] : null,
        ];
    }

    /**
     * @param string $name Constant name
     * @return array
     */
    public function getConstantInfo($name)
    {
        $name = ltrim($name, '\\');
        return [
            'addition' => isset($this->additions['constant'][ $name ]) ? $this->additions['constant'][ $name ] : null,
            'deprecation' => isset($this->deprecations['constant'][ $name ]) ? $this->deprecations['constant'][ $name ] : null,
            'removal' => isset($this->removals['constant'][ $name ]) ? $this->removals['constant'][ $name ] : null,
        ];
    }
}
