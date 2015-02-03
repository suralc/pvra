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


class LibraryInformation implements LibraryInformationInterface
{
    private $additions = [];
    private $deprecations = [];
    private $removals = [];

    public static function createWithDefaults()
    {
        $source = __DIR__ . '/../../data/library/php/changes.php';

        if (!is_file($source) || !is_readable($source)) {
            throw new \InvalidArgumentException(sprintf('The file "%s" does not exist or is not readable',
                $source));
        }

        return new static(include $source);
    }


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

        $keys = ['class', 'function', 'constant'];
        foreach (['additions', 'deprecations', 'removals'] as $propName) {
            foreach ($keys as $key) {
                if (!isset($this->{$propName}[ $key ])) {
                    $this->{$propName}[ $key ] = [];
                }
            }
        }
    }

    public function toArray()
    {
        return [
            'additions' => $this->additions,
            'deprecations' => $this->deprecations,
            'removals' => $this->removals,
        ];
    }

    public function mergeInformation(LibraryInformationInterface $info)
    {
        $newInfo = $info->toArray();
        $this->additions = array_replace_recursive($this->additions, $newInfo['additions']);
        $this->deprecations = array_replace_recursive($this->deprecations, $newInfo['deprecations']);
        $this->removals = array_replace_recursive($this->removals, $newInfo['removals']);
        return $this;
    }

    /**
     * @param string $name
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
     * @param string $name
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
     * @param string $name
     * @return array
     */
    public function getConstantInfo($name)
    {
        $name = ltrim($name, '\\');
        return [
            'addition' => isset($this->additions['constant'][ $name ]) ? $this->additions['constant'][ $name ] : null,
            'deprecation' => isset($this->deprecations['constant'][ $name ]) ? $this->deprecations['constant'][ $name ] : null,
            'removal' => isset($this->deprecations['constant'][ $name ]) ? $this->deprecations['constant'][ $name ] : null,
        ];
    }
}
