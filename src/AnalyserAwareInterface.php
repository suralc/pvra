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
 * @author     suralc <suralc.github@gmail.com>
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
     * Set or override the owning analyser
     *
     * @param \Pvra\Analyser $requirementAnalyser The owning analyser.
     * @return $this
     */
    public function setOwningAnalyser(Analyser $requirementAnalyser);

    /**
     * Retrieve the owning analyser
     *
     * If no analyser has been set `null` will be returned
     *
     * @return \Pvra\Analyser|null
     */
    public function getOwningAnalyser();
}
