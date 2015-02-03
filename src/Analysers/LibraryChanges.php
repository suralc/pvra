<?php
/**
 * LibraryChanges.php
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

namespace Pvra\Analysers;


use Pvra\Analyser;
use Pvra\AnalyserAwareInterface;
use Pvra\InformationProvider\LibraryInformation;
use Pvra\InformationProvider\LibraryInformationAwareInterface;
use Pvra\InformationProvider\LibraryInformationInterface;

abstract class LibraryChanges extends LanguageFeatureAnalyser implements AnalyserAwareInterface, LibraryInformationAwareInterface
{
    /**
     * @var LibraryInformation
     */
    private $information;

    /**
     * @inheritdoc
     */
    public function __construct($information = null, array $options = [], Analyser $analyser = null)
    {
        parent::__construct($options, $analyser);

        if ($information instanceof LibraryInformation) {
            $this->information = $information;
        } elseif ($information === null) {
            $this->information = LibraryInformation::createWithDefaults();
        } else {
            if (is_string($information)) {
                if (!file_exists($information) || !is_readable($information)) {
                    throw new \InvalidArgumentException(sprintf('The file "%s" does not exist or is not readable',
                        $information));
                }

                $this->information = new LibraryInformation(include $information);
            } elseif (is_array($information)) {
                $this->information = new LibraryInformation($information);
            } else {
                throw new \InvalidArgumentException(sprintf('The $information parameter has to be an instance of LibraryInformation, string or an array. %s given.',
                    gettype($information) === 'object' ? get_class($information) : gettype($information)));
            }
        }
    }

    // todo remove LibraryAdditions, move logic to LibraryInformation and only match names in this class
    public function setLibraryInformation(LibraryInformationInterface $libInfo)
    {
        $this->information = $libInfo;

        return $this;
    }

    /**
     * @return LibraryInformationInterface
     */
    public function getLibraryInformation()
    {
        return $this->information;
    }

}
