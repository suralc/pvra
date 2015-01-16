<?php
/**
 * AnalyserAwareInterface.php
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
 * @author     suralc <thesurwaveing@gmail.com>
 * @license    http://opensource.org/licenses/MIT  MIT
 */
namespace Pvra;


use PhpParser\NodeVisitor;

/**
 * Interface AnalyserAwareInterface
 *
 * @package Pvra
 */
interface AnalyserAwareInterface extends NodeVisitor
{
    /**
     * @param \Pvra\Analyser $requirementAnalyser
     * @return void
     */
    public function setOwningAnalyser(Analyser $requirementAnalyser);

    /**
     * @return \Pvra\Analyser
     */
    public function getOwningAnalyser();
}
