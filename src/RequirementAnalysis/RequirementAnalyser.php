<?php

namespace Pvra\RequirementAnalysis;


use PhpParser\Lexer\Emulative;
use PhpParser\NodeTraverser;
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
     * @var NodeTraverser
     */
    private $nodeTraverser;

    /**
     * @var RequirementAnalysisResult
     */
    private $result;

    /**
     *
     */
    public function __construct($registerNameResolver = true)
    {
        $this->initBaseParser();
        $this->initTraverser();

        if ($registerNameResolver === true) {
            $this->getNodeTraverser()->addVisitor(new NodeVisitor\NameResolver());
        }

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
     * @return \PhpParser\Node[]
     */
    protected abstract function parse();

    /**
     * @return string
     */
    protected abstract function createAnalysisTargetId();

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
     * @return Parser
     */
    public function getParser()
    {
        return $this->parser;
    }

    /**
     * @return bool
     */
    public function isParserInitiated()
    {
        return $this->parser instanceof Parser;
    }

    /**
     * @return bool
     */
    public function isTraverserInitiated()
    {
        return $this->nodeTraverser instanceof NodeTraverser;
    }

    /**
     * @return NodeTraverser
     */
    public function getNodeTraverser()
    {
        return $this->nodeTraverser;
    }

    /**
     * @param Parser $parser
     */
    protected function setParser(Parser $parser)
    {
        $this->parser = $parser;
    }

    /**
     * @param NodeTraverser $nodeTraverser
     */
    protected function setTraverser(NodeTraverser $nodeTraverser)
    {
        $this->nodeTraverser = $nodeTraverser;
    }

    /**
     *
     */
    private function initBaseParser()
    {
        $this->setParser(new Parser(new Emulative()));
    }

    /**
     *
     */
    private function initTraverser()
    {
        $this->setTraverser(new NodeTraverser);
    }
}