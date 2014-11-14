<?php

namespace Pvra\tests;


use Mockery as m;
use PhpParser\Lexer\Emulative;
use PhpParser\NodeTraverser;
use PhpParser\Parser;
use Pvra\PhpParser\RequirementAnalyserAwareInterface;
use Pvra\RequirementAnalysis\RequirementAnalysisResult;

/**
 * Class BaseNodeWalkerTestCase
 *
 * TODO:
 * Only indirect tests for now. Either rethink the tests (maybe test on serialized nodes directly) or modify
 * RequirementAnalyserAwareInterface.
 *
 * Or mock Result and Analyser
 *
 * @package Pvra\tests
 */
class BaseNodeWalkerTestCase extends \PHPUnit_Framework_TestCase
{
    protected $classToTest;

    /**
     * @return array
     */
    protected function buildTestInstances()
    {
        if (is_string($this->classToTest) && class_exists($this->classToTest)) {

            $result = new RequirementAnalysisResult();
            $analyserMock = m::mock('Pvra\\RequirementAnalysis\\RequirementAnalyser');
            $analyserMock->shouldReceive('getResult')->andReturn($result);

            $className = $this->classToTest;
            return [new $className($analyserMock), $result];
        }

        $this->fail(sprintf('Could not build test instance of %s', $this->classToTest));

        return [];
    }

    public function tearDown()
    {
        m::close();
    }

    protected function getAstNodesFromFile($file)
    {
        $file = TEST_FILE_ROOT . '/' . $file . '.php';

        $parser = new Parser(new Emulative());

        return $parser->parse(file_get_contents($file));
    }

    protected function traverseInstanceOverStmts($stmts, RequirementAnalyserAwareInterface $walker)
    {
        $traverser = new NodeTraverser();

        $traverser->addVisitor($walker);

        return $traverser->traverse($stmts);
    }

    /**
     * @param $file
     * @return RequirementAnalysisResult
     */
    protected function runInstanceFromScratch($file)
    {
        list($ins, $result) = $this->buildTestInstances();

        $this->traverseInstanceOverStmts($this->getAstNodesFromFile($file), $ins);

        return $result;
    }
}