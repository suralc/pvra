<?php

namespace Pvra\RequirementAnalysis;


use PhpParser\Lexer\Emulative;
use PhpParser\NodeTraverser;
use PhpParser\NodeTraverserInterface;
use PhpParser\NodeVisitor;
use PhpParser\Parser;
use Pvra\PhpParser\RequirementAnalyserAwareInterface;

abstract class RequirementAnalyser
{
    /**
     * @var Parser
     */
    private $parser;

    /**
     * @var NodeTraverserInterface
     */
    private $nodeTraverser;

    /**
     * @var RequirementAnalysisResult
     */
    private $result;

    /**
     * @param bool $registerNameResolver If set to true `PhpParser\NodeVisitor\NameResolver` will be added as the first
     *     visitor. This may negatively affect performance, some Visitors depend on resolved names, however.
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
     * @see RequirementAnalyser::initDefaultTraverser
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
     *
     */
    private function initDefaultTraverser()
    {
        $this->setTraverser(new NodeTraverser);
    }

    /**
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
     */
    public function attachRequirementVisitor(RequirementAnalyserAwareInterface $visitor)
    {
        $visitor->setOwningAnalyser($this);
        $this->getNodeTraverser()->addVisitor($visitor);

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
     * @return bool
     */
    public function isAnalyserRun()
    {
        return $this->result instanceof RequirementAnalysisResult && $this->result->isSealed();
    }

    /**
     * @return RequirementAnalysisResult
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
     * @param \Pvra\RequirementAnalysis\RequirementAnalysisResult $result
     * @throws \Exception
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
    }

    /**
     * Parse the given code.
     *
     * Implementations of this method should parse the given code and return a list of `Node` instances.
     * The simplest implementation may directly return `return $this->getParser()->getParse($this->getCode());`.
     *
     * @return \PhpParser\Node[]
     */
    protected abstract function parse();

    /**
     * Create an identifier for the parsed content.
     *
     * Implementations of this method should return a string that can be used to identify a given source.
     * This may be achieved by hashing a given string or returning an absolute path.
     *
     * @return string
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
        $this->setParser(new Parser(new Emulative()));
    }
}
