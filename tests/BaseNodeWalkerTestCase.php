<?php

namespace Pvra\tests;


use Mockery as m;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\Parser;
use Pvra\AnalyserAwareInterface;
use Pvra\AnalysisResult;
use Pvra\Lexer\ExtendedEmulativeLexer;

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
    protected $expandNames = false;

    /**
     * @return array
     */
    protected function buildTestInstances()
    {
        if (is_string($this->classToTest) && class_exists($this->classToTest)) {
            $result = new AnalysisResult();
            $analyserMock = m::mock('Pvra\\Analyser');
            $analyserMock->shouldReceive('getResult')->andReturn($result);

            $className = $this->classToTest;
            return [new $className($analyserMock), $result];
        }

        $this->fail(sprintf('Could not build test instance of %s',
            $this->classToTest !== null ? $this->classToTest : '?. $classToTest was not set.'));

        return [];
    }

    public function tearDown()
    {
        m::close();
    }

    protected function runTestsAgainstExpectation(array $expected, $file, $version = null)
    {
        $result = $this->runInstanceFromScratch($file);

        $this->assertCount(count($expected), $result);

        if($version !== null) {
            $this->assertSame($version, $result->getRequiredVersion());
        }

        $resultIt = $result->getIterator();

        foreach ($expected as $expectation) {
            if(!$resultIt->valid()){
                $this->fail('Unexpected end of iterator.');
            }
            $this->assertSame($expectation[0], $resultIt->current()['line']);
            $this->assertSame($expectation[1], $resultIt->current()['reason']);
            $resultIt->next();
        }
    }

    protected function getAstNodesFromFile($file)
    {
        $file = TEST_FILE_ROOT . '/' . $file . '.php';

        $parser = new Parser(ExtendedEmulativeLexer::createDefaultInstance());

        $stmts = $parser->parse(file_get_contents($file));

        if ($this->expandNames) {
            $trav = new NodeTraverser();
            $trav->addVisitor(new NameResolver());
            $stmts = $trav->traverse($stmts);
        }

        return $stmts;
    }

    protected function traverseInstanceOverStmts($stmts, AnalyserAwareInterface $walker)
    {
        $traverser = new NodeTraverser();

        $traverser->addVisitor($walker);

        return $traverser->traverse($stmts);
    }

    /**
     * @param $file
     * @return AnalysisResult
     */
    protected function runInstanceFromScratch($file)
    {
        list($ins, $result) = $this->buildTestInstances();

        $this->traverseInstanceOverStmts($this->getAstNodesFromFile($file), $ins);

        return $result;
    }
}
