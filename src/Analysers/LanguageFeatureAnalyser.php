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
     * The `Analyser` representing the currently running operation.
     *
     * @var Analyser
     */
    private $requirementAnalyser;
    /**
     * @var array
     */
    private $options;

    /**
     * Create an instance of the child Analyser
     *
     * It is optional to set the related analyser during instance creation.
     * When using this class in the context of an `Analyser` it will always be ensured
     * that this `Analyser` will be known to the instance before any node is traversed.
     *
     * @param array $options
     * @param \Pvra\Analyser $analyser
     * @see setOwningAnalyser() Set the owning analyser
     */
    public function __construct(array $options = [], Analyser $analyser = null)
    {
        $this->options = $options;
        if($analyser !== null) {
            $this->setOwningAnalyser($analyser);
        }
    }

    /**
     * @inheritdoc
     */
    public function setOwningAnalyser(Analyser $requirementAnalyser)
    {
        $this->requirementAnalyser = $requirementAnalyser;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getOwningAnalyser()
    {
        return $this->requirementAnalyser;
    }

    /**
     * Get the  instance of the currently used `Pvra\AnalysisResult`.
     *
     * @return \Pvra\AnalysisResult
     * @see Analyser::getResult() Method used to retrieve the result
     */
    protected function getResult()
    {
        return $this->getOwningAnalyser()->getResult();
    }

    /**
     * @param string|int $name
     * @param mixed $default
     * @return mixed
     */
    protected function getOption($name, $default = null)
    {
        return isset($this->options[ $name ]) ? $this->options[ $name ] : $default;
    }

    /**
     * @return array
     */
    protected function getOptions()
    {
        return $this->options;
    }

    /**
     * Called when entering a source node
     *
     * This method is called when a source node is entered. Contained logic determines the presence of
     * specific syntactical features.
     *
     * @param \PhpParser\Node $node The node to parse.
     * @return null|Node The nodes should not be modified as other walkers might depend on it.
     * @see getResult() ResultInstance
     * @see AnalysisResult::addRequirement() Add new requirement
     * @see AnalysisResult::addArbitraryRequirement() Add new arbitrary requirement
     * @codeCoverageIgnore
     */
    public function enterNode(Node $node)
    {
    }
}
