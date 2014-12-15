<?php
/**
 * RequirementAnalyserAwareInterface.php
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
namespace Pvra\PhpParser;


use PhpParser\NodeVisitor;
use Pvra\RequirementAnalysis\RequirementAnalyser;

/**
 * Interface RequirementAnalyserAwareInterface
 *
 * @package Pvra\PhpParser
 */
interface RequirementAnalyserAwareInterface extends NodeVisitor
{
    /**
     * @param RequirementAnalyser $requirementAnalyser
     * @return void
     */
    public function setOwningAnalyser(RequirementAnalyser $requirementAnalyser);

    /**
     * @return RequirementAnalyser
     */
    public function getOwningAnalyser();
}
