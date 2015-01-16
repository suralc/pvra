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
namespace Pvra\Analysers;


use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;
use Pvra\Analyser;
use Pvra\AnalyserAwareInterface;

/**
 * Class LanguageFeatureAnalyser
 *
 * @package Pvra\PhpParser\Analysers
 */
abstract class LanguageFeatureAnalyser extends NodeVisitorAbstract implements AnalyserAwareInterface
{
    /**
     * @var Analyser
     */
    private $requirementAnalyser;

    /**
     * @param \Pvra\Analyser $requirementAnalyser
     */
    public function __construct(Analyser $requirementAnalyser = null)
    {
        if ($requirementAnalyser !== null) {
            $this->setOwningAnalyser($requirementAnalyser);
        }
    }

    /**
     * @param Analyser $requirementAnalyser
     */
    public function setOwningAnalyser(Analyser $requirementAnalyser)
    {
        $this->requirementAnalyser = $requirementAnalyser;
    }

    /**
     * @return Analyser
     */
    public function getOwningAnalyser()
    {
        return $this->requirementAnalyser;
    }

    /**
     * Get the result instance of the currently attached Analyser.
     *
     * @return \Pvra\AnalysisResult
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
