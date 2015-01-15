<?php
/**
 * LanguageFeatureAnalyser.php
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
namespace Pvra\PhpParser\Analysers;


use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;
use Pvra\PhpParser\RequirementAnalyserAwareInterface;
use Pvra\RequirementAnalysis\RequirementAnalyser;

/**
 * Class LanguageFeatureAnalyser
 *
 * @package Pvra\PhpParser\Analysers
 */
abstract class LanguageFeatureAnalyser extends NodeVisitorAbstract implements RequirementAnalyserAwareInterface
{
    /**
     * @var RequirementAnalyser
     */
    private $requirementAnalyser;

    /**
     * @param \Pvra\RequirementAnalysis\RequirementAnalyser $requirementAnalyser
     */
    public function __construct(RequirementAnalyser $requirementAnalyser = null)
    {
        if ($requirementAnalyser !== null) {
            $this->setOwningAnalyser($requirementAnalyser);
        }
    }

    /**
     * @param RequirementAnalyser $requirementAnalyser
     */
    public function setOwningAnalyser(RequirementAnalyser $requirementAnalyser)
    {
        $this->requirementAnalyser = $requirementAnalyser;
    }

    /**
     * @return RequirementAnalyser
     */
    public function getOwningAnalyser()
    {
        return $this->requirementAnalyser;
    }

    /**
     * Get the result instance of the currently attached Analyser.
     *
     * @return \Pvra\RequirementAnalysis\RequirementAnalysisResult
     */
    protected function getResult()
    {
        return $this->getOwningAnalyser()->getResult();
    }

    /**
     * Called when entering a source node
     *
     * This method is called when a source node is entered. Contained logic determines the presence of
     * specific syntactical features.
     *
     * @param \PhpParser\Node $node The node to parse.
     * @return null The nodes should not be modified as other walkers might depend on it.
     * @see getResult() ResultInstance
     * @see RequirementAnalysisResult::addRequirement() Add new requirement
     * @codeCoverageIgnore
     */
    public function enterNode(Node $node)
    {
    }
}
