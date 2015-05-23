<?php
/**
 * LibraryInformationInterface.php
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
 * Interface LibraryInformationInterface
 *
 * @package Pvra\InformationProvider
 */
interface LibraryInformationInterface
{
    /**
     * Get the array representation of the instance
     *
     * @return array Array representation of the data stored in this object
     */
    public function toArray();

    /**
     * Merge another instance into this instance
     *
     * @param \Pvra\InformationProvider\LibraryInformationInterface $info
     * @return $this
     */
    public function mergeWith(LibraryInformationInterface $info);

    /**
     * Retrieve information about a given function
     *
     * @param string $name Function name
     * @return array An array in the format:
     * <code>
     * ['addition' => Version|null, 'deprecation' => Version|null, 'removal' => Version|null]
     * </code>
     * @see getInfo() Underlying function for array generation
     */
    public function getFunctionInfo($name);

    /**
     * Retrieve information about a given class
     *
     * @param string $name Class name
     * @return array An array in the format:
     * <code>
     * ['addition' => Version|null, 'deprecation' => Version|null, 'removal' => Version|null]
     * </code>
     * @see getInfo() Underlying function for array generation
     */
    public function getClassInfo($name);

    /**
     * Retrieve information about a given constant
     *
     * @param string $name Constant name
     * @return array An array in the format:
     * <code>
     * ['addition' => Version|null, 'deprecation' => Version|null, 'removal' => Version|null]
     * </code>
     */
    public function getConstantInfo($name);
}
