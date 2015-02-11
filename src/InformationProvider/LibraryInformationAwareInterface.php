<?php
/**
 * LibraryInformationAwareInterface.php
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
 * Interface LibraryInformationAwareInterface
 *
 * @package Pvra\InformationProvider
 */
interface LibraryInformationAwareInterface
{
    /**
     * @param \Pvra\InformationProvider\LibraryInformationInterface $libInfo
     * @return $this
     */
    public function setLibraryInformation(LibraryInformationInterface $libInfo);

    /**
     * @param \Pvra\InformationProvider\LibraryInformationInterface $libInfo
     * @return $this
     */
    public function addLibraryInformation(LibraryInformationInterface $libInfo);

    /**
     * @return LibraryInformationInterface
     */
    public function getLibraryInformation();
}
