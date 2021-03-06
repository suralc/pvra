<?php
/**
 * Analyser.php
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


use PhpParser\NodeTraverser;
use PhpParser\NodeTraverserInterface;
use PhpParser\NodeVisitor;
use PhpParser\Parser;
use PhpParser\ParserFactory;
use Pvra\Lexer\ExtendedEmulativeLexer;

/**
 * Class Analyser
 *
 * @package Pvra
 */
abstract class Analyser
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
     * @var AnalysisResult
     */
    private $result;

    /**
     * Analyser constructor
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
     * @see RequirementAnalyser::initDefaultTraverser Analyser::initDefaultTraverser
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
     * Will return true if any traverser has previously been set. Will also return true if the
     * default/fallback traverser has been attached.
     *
     * @return bool Returns true when Analyser::$nodeTraverser has been initialized.
     */
    public function hasNodeTraverserAttached()
    {
        return $this->nodeTraverser !== null;
    }

    /**
     * Initiate this instance using a default Traverser
     *
     * This method will attach the NodeTraverser shipped within the PHP-Parser dependency as default traverser.
     * Usually `PhpParser\NodeTraverser`
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
     * @param NodeVisitor|AnalyserAwareInterface $visitor
     * @return $this Returns the current instance to allow chained calls.
     * @see RequirementAnalyserAwareInterface::setOwningAnalyser() Current instance is correctly attached.s
     */
    public function attachRequirementVisitor(AnalyserAwareInterface $visitor)
    {
        $visitor->setOwningAnalyser($this);
        $this->getNodeTraverser()->addVisitor($visitor);

        return $this;
    }

    /**
     * Attach an array of RequirementVisitors
     *
     * This method can be used to attach multiple `RequirementAnalyserAwareInterface` instances at once.
     * `Analyser::attachRequirementVisitor` is called upon each instance.
     *
     * @param AnalyserAwareInterface[] $visitors An array of requirement visitors
     * @return $this Returns the current instance to allow chained calls.
     * @see attachRequirementVisitor() Method containing implementation
     */
    public function attachRequirementVisitors(array $visitors)
    {
        foreach ($visitors as $visitor) {
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
     * @return AnalysisResult The sealed result.
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
     * The result is based on the presence of a `AnalysisResult` instance and its state.
     *
     * @return bool Whether the analyser has a result instance and it was sealed.
     */
    public function isAnalyserRun()
    {
        return $this->result instanceof AnalysisResult && $this->result->isSealed();
    }

    /**
     * Get the result instance associated with this Analyser
     *
     * If a `AnalysisResult` instance is attached it will be returned.
     * If none was attached a default one is attached. Please be aware that you cannot set
     * a custom `MessageFormatter` in an instance created using this.
     *
     * @return AnalysisResult The `AnalysisResult` instance attached with this
     * analyser
     *
     * @see setResultInstance() Used to set an instance of a result.
     */
    public function getResult()
    {
        if ($this->result === null) {
            $this->result = new AnalysisResult();
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
     * @param \Pvra\AnalysisResult $result The `AnalysisResult` to be
     * attached to this analyser
     * @return $this Returns the current instance to allow method chaining
     * @throws \Exception Thrown in case that the given result is sealed or an Result instance was already attached.
     *
     * @see RequirementAnalysisResult::setAnalysisTargetId() Method that is called on the attached result instance
     */
    public function setResultInstance(AnalysisResult $result)
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
     * Get the currently injected parser or create a default instance
     *
     * This method will always return a valid `Parser` instance, even if none was injected.
     * In that case the return value of `initDefaultParser` will be returned
     *
     * @return Parser
     * @see setParser() Inject a custom `Parser` object
     * @see initDefaultParser() Create and set a fallback `Parser` object
     */
    public function getParser()
    {
        if (!$this->hasParserAttached()) {
            $this->initDefaultParser();
        }

        return $this->parser;
    }

    /**
     * Inject a custom `Parser` object
     *
     * Calling this method will override any previously injected parser object.
     *
     * @param Parser $parser
     */
    protected function setParser(Parser $parser)
    {
        $this->parser = $parser;
    }

    /**
     * Returns whether a parser was attached.
     *
     * Will return true if any parserhas previously been set. Will also return true if the
     * default/fallback parser has been attached.
     *
     * @return bool
     */
    public function hasParserAttached()
    {
        return $this->parser !== null;
    }

    /**
     * Set a fallback parser as parser for this Analyser instance
     *
     * The used `Parser` object will use the `ExtendedEmulativeLexer`.
     *
     * @see ExtendedEmulativeLexer Used lexer
     */
    private function initDefaultParser()
    {
        $this->setParser((new ParserFactory())
            ->create(ParserFactory::PREFER_PHP7, ExtendedEmulativeLexer::createDefaultInstance())
        );
    }
}
