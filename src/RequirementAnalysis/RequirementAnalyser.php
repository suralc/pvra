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
     * @param bool $registerNameResolver
     */
    public function __construct($registerNameResolver = true)
    {
        if ($registerNameResolver === true) {
            $this->getNodeTraverser()->addVisitor(new NodeVisitor\NameResolver());
        }
    }

    /**
     * @return NodeTraverserInterface
     */
    public function getNodeTraverser()
    {
        if (!$this->hasNodeTraverserAttached()) {
            $this->initDefaultTraverser();
        }

        return $this->nodeTraverser;
    }

    /**
     * @return bool
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
     * @param NodeVisitor|RequirementAnalyserAwareInterface $visitor
     */
    public function attachRequirementAnalyser(RequirementAnalyserAwareInterface $visitor)
    {
        $visitor->setOwningAnalyser($this);
        $this->getNodeTraverser()->addVisitor($visitor);
    }

    /**
     * @return RequirementAnalysisResult
     */
    public function run()
    {
        if ($this->isAnalyserRun()) {
            return $this->getResult();
        }

        $stmts = $this->parse();

        // RequirementAnalyserAwareInterface visitors will call getResult on this instance.
        $this->nodeTraverser->traverse($stmts);

        $this->getResult()->seal();

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
     * @return \PhpParser\Node[]
     */
    protected abstract function parse();

    /**
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
