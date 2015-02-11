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
 * @author     suralc <thesurwaveing@gmail.com>
 * @license    http://opensource.org/licenses/MIT  MIT
 */

namespace Pvra\InformationProvider;


interface LibraryInformationInterface
{
    /**
     * @return array
     */
    public function toArray();

    /**
     * @param \Pvra\InformationProvider\LibraryInformationInterface $info
     * @return $this
     */
    public function mergeWith(LibraryInformationInterface $info);

    /**
     * @param string $name
     * @return array
     */
    public function getFunctionInfo($name);

    /**
     * @param string $name
     * @return array
     */
    public function getClassInfo($name);

    /**
     * @param string $name
     * @return array
     */
    public function getConstantInfo($name);
}
