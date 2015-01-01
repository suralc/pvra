<?php
/**
 * RequirementAnalyser.php
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
namespace Pvra\RequirementAnalysis;


use PhpParser\NodeTraverser;
use PhpParser\NodeTraverserInterface;
use PhpParser\NodeVisitor;
use PhpParser\Parser;
use Pvra\PhpParser\Lexer\ExtendedEmulativeLexer;
use Pvra\PhpParser\RequirementAnalyserAwareInterface;

/**
 * Class RequirementAnalyser
 *
 * @package Pvra\RequirementAnalysis
 */
abstract class RequirementAnalyser
{
    /**
     * The instance of Parser used to run this analysis.
     *
     * @var Parser
     */
    private $parser;

    /**
     * The NodeTraverserInterface used to run this analysis.
     *
     * @var NodeTraverserInterface
     */
    private $nodeTraverser;

    /**
     * The result-instance used to store results from this analysis.
     *
     * @var RequirementAnalysisResult
     */
    private $result;

    /**
     * RequirementAnalyser constructor
     *
     * This constructor registers the `NodeVisitor\NameResolver` node walker as first node traverser if the first
     * argument is set to true. Refer to the parameters documentation.
     *
     * @param bool $registerNameResolver If set to true `PhpParser\NodeVisitor\NameResolver` will be added as the first
     * visitor. This may negatively affect performance, some Visitors depend on resolved names, however.
     */
    public function __construct($registerNameResolver = true)
    {
        if ($registerNameResolver === true) {
            $this->getNodeTraverser()->addVisitor(new NodeVisitor\NameResolver());
        }
    }

    /**
     * Gets the associated NodeTraverser
     *
     * This method returns the associated NodeTraverser. If no NodeTraverser has been attached a default one will be
     * created.
     *
     * @see RequirementAnalyser::initDefaultTraverser RequirementAnalyser::initDefaultTraverser
     *
     * @return NodeTraverserInterface The NodeTraverser associated to this instance.
     */
    public function getNodeTraverser()
    {
        if (!$this->hasNodeTraverserAttached()) {
            $this->initDefaultTraverser();
        }

        return $this->nodeTraverser;
    }

    /**
     * Check whether a NodeTraverser has been attached to the current Analyser
     *
     * @return bool Returns true when RequirementAnalyser::$nodeTraverser has been initialized.
     */
    public function hasNodeTraverserAttached()
    {
        return $this->nodeTraverser !== null;
    }

    /**
     * Initiate this instance using the default Traverser delivered by PHP-Parser
     */
    private function initDefaultTraverser()
    {
        $this->setTraverser(new NodeTraverser);
    }

    /**
     * Set the NodeTraverser used by this instance
     *
     * This method allows you to set a `NodeTraverser` before the default one is initialized.
     * This should usually happen in the constructor of a child class.
     *
     * @param \PhpParser\NodeTraverser|\PhpParser\NodeTraverserInterface $nodeTraverser
     */
    protected function setTraverser(NodeTraverserInterface $nodeTraverser)
    {
        $this->nodeTraverser = $nodeTraverser;
    }

    /**
     * Attaches a RequirementVisitor.
     *
     * This method should be used to attach an instance of `Pvra\PhpParser\RequirementAnalyserAwareInterface` to the
     * current analyser. This method makes sure that RequirementAnalyserAwareInterface::setOwningAnalyser is
     * called using the correct parameters.
     *
     * @param NodeVisitor|RequirementAnalyserAwareInterface $visitor
     * @return $this Returns the current instance to allow chained calls.
     * @see RequirementAnalyserAwareInterface::setOwningAnalyser() Current instance is correctly attached.s
     */
    public function attachRequirementVisitor(RequirementAnalyserAwareInterface $visitor)
    {
        $visitor->setOwningAnalyser($this);
        $this->getNodeTraverser()->addVisitor($visitor);

        return $this;
    }

    /**
     * Attach an array of RequirementVisitors
     *
     * This method can be used to attach multiple `RequirementAnalyserAwareInterface` instances at once.
     * `RequirementAnalyser::attachRequirementVisitor` is called upon each instance.
     *
     * @param RequirementAnalyserAwareInterface[] $visitors An array of requirement visitors
     * @return $this Returns the current instance to allow chained calls.
     * @see attachRequirementVisitor() Method containing implementation
     */
    public function attachRequirementVisitors($visitors) {
        foreach($visitors as $visitor) {
            $this->attachRequirementVisitor($visitor);
        }

        return $this;
    }

    /**
     * Execute the current Analysis
     *
     * Parses the given code and runs the currently attached visitors. If run has already been called the previously
     * generated result will be returned. The result instance returned by this method is sealed.
     * Visitors that are attached **after** run is called are ignored on subsequent calls.
     *
     * @see RequirementAnalysisResult::seal
     *
     * @return RequirementAnalysisResult The sealed result.
     */
    public function run()
    {
        if (!$this->isAnalyserRun()) {
            $stmts = $this->parse();

            // RequirementAnalyserAwareInterface visitors will call getResult on this instance.
            $this->nodeTraverser->traverse($stmts);

            $this->getResult()->seal();
        }

        return $this->getResult();
    }

    /**
     * Determines of this analyser is already run
     *
     * The result is based on the presence of a `RequirementAnalysisResult` instance and its state.
     *
     * @return bool Whether the analyser has a result instance and it was sealed.
     */
    public function isAnalyserRun()
    {
        return $this->result instanceof RequirementAnalysisResult && $this->result->isSealed();
    }

    /**
     * Get the result instance associated with this RequirementAnalyser
     *
     * If a `RequirementAnalysisResult` instance is attached it will be returned.
     * If none was attached a default one is attached. Please be aware that you cannot set
     * a custom `ResultMessageFormatter` in an instance created using this.
     *
     * @return RequirementAnalysisResult The `RequirementAnalysisResult` instance attached with this
     * analyser
     *
     * @see setResultInstance() Used to set an instance of a result.
     */
    public function getResult()
    {
        if ($this->result === null) {
            $this->result = new RequirementAnalysisResult();
            $this->result->setAnalysisTargetId($this->createAnalysisTargetId());
        }

        return $this->result;
    }

    /**
     * Attach a (new) ResultInstance to this analyser
     *
     * Set a new result instance to this analyser. If a result was already attached or the to be attached result is
     * already sealed an exception is thrown.
     *
     * @param \Pvra\RequirementAnalysis\RequirementAnalysisResult $result The `RequirementAnalysisResult` to be
     * attached to this analyser
     * @return $this Returns the current instance to allow method chaining
     * @throws \Exception Thrown in case that the given result is sealed or an Result instance was already attached.
     *
     * @see RequirementAnalysisResult::setAnalysisTargetId() Method that is called on the attached result instance
     */
    public function setResultInstance(RequirementAnalysisResult $result)
    {
        if ($this->result !== null) {
            throw new \Exception('A result instance was already set. Overriding it may lead to data loss.');
        } elseif ($result->isSealed()) {
            throw new \LogicException('The attached Result instance is already sealed.');
        }

        $this->result = $result;
        $this->result->setAnalysisTargetId($this->createAnalysisTargetId());

        return $this;
    }

    /**
     * Parse the given code.
     *
     * Implementations of this method should parse the given code and return a list of `Node` instances.
     * The simplest implementation may directly return `return $this->getParser()->getParse($this->getCode());`.
     *
     * @return \PhpParser\Node[] List of nodes representing the analysing target.
     */
    protected abstract function parse();

    /**
     * Create an identifier for the parsed content.
     *
     * Implementations of this method should return a string that can be used to identify a given source.
     * This may be achieved by hashing a given string or returning an absolute path.
     *
     * @return string Identifier to identify the target of this analyser.
     */
    protected abstract function createAnalysisTargetId();

    /**
     * @return Parser
     */
    public function getParser()
    {
        if (!$this->hasParserAttached()) {
            $this->initDefaultParser();
        }
        return $this->parser;
    }

    /**
     * @param Parser $parser
     */
    protected function setParser(Parser $parser)
    {
        $this->parser = $parser;
    }

    /**
     * @return bool
     */
    public function hasParserAttached()
    {
        return $this->parser !== null;
    }

    /**
     *
     */
    private function initDefaultParser()
    {
        $this->setParser(new Parser(new ExtendedEmulativeLexer()));
    }
}
