<?php

namespace Pvra\tests;


use Mockery as m;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\Parser;
use Pvra\AnalyserAwareInterface;
use Pvra\Analysers\LanguageFeatureAnalyser;
use Pvra\AnalysisResult;
use Pvra\Lexer\ExtendedEmulativeLexer;
use Pvra\Result\Reason;

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
    protected function buildTestInstances($mode)
    {
        if (is_string($this->classToTest) && class_exists($this->classToTest)) {
            $result = new AnalysisResult();
            $analyserMock = m::mock('Pvra\\Analyser');
            $analyserMock->shouldReceive('getResult')->andReturn($result);

            $className = $this->classToTest;
            return [(new $className(['mode' => $mode]))->setOwningAnalyser($analyserMock), $result];
        }

        $this->fail(sprintf('Could not build test instance of %s',
            $this->classToTest !== null ? $this->classToTest : '?. $classToTest was not set.'));

        return [];
    }

    public function tearDown()
    {
        m::close();
    }

    /**
     * @param array $expected
     * @param string $file
     * @param null|string $version
     * @param int $mode
     */
    protected function runTestsAgainstExpectation(
        array $expected,
        $file,
        $version = null,
        $mode = LanguageFeatureAnalyser::MODE_ALL
    ) {
        $result = $this->runInstanceFromScratch($file, $mode);

        $this->assertCount(count($expected), $result);

        if ($version !== null) {
            if ($version{0} === '-') {
                $this->assertSame(ltrim($version, '-'), $result->getVersionLimit());
            } else {
                $this->assertSame($version, $result->getRequiredVersion());
            }
        }

        $resultIt = $result->getIterator();

        foreach ($expected as $expectation) {
            if (!$resultIt->valid()) {
                $this->fail('Unexpected end of iterator.');
            }
            $this->assertSame($expectation[0], $resultIt->current()['line']);
            $this->assertSame($expectation[1], $resultIt->current()['reason'],
                sprintf('Expected reason %d(%s) got %d(%s)', $expectation[1],
                    Reason::getReasonNameFromValue($expectation[1]), $resultIt->current()['reason'],
                    Reason::getReasonNameFromValue($resultIt->current()['reason'])));
            if (isset($expectation[2])) {
                $this->assertArraySubset($expectation[2], $resultIt->current()['data']);
            }
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
     * @param string $file
     * @param int $mode
     * @return AnalysisResult
     */
    protected function runInstanceFromScratch($file, $mode)
    {
        list($ins, $result) = $this->buildTestInstances($mode);

        $this->traverseInstanceOverStmts($this->getAstNodesFromFile($file), $ins);

        return $result;
    }
}
