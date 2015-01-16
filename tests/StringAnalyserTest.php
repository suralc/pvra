<?php

namespace Pvra\tests;


use Mockery as m;
use Pvra\AnalysisResult;
use Pvra\StringAnalyser;

/**
 * Class StringAnalyserTest
 *
 * These tests cover the abstract base class.
 *
 * @package Pvra\tests\RequirementAnalysis
 */
class StringAnalyserTest extends \PHPUnit_Framework_TestCase
{
    const DEFAULT_PLACEHOLDER_INPUT = '<?php trait abc {}';

    public function testInit()
    {
        $a = new StringAnalyser(self::DEFAULT_PLACEHOLDER_INPUT);
        $this->assertInstanceOf('PhpParser\\Parser', $a->getParser());
        $this->assertInstanceOf('PhpParser\\NodeTraverser', $a->getNodeTraverser());
    }

    public function testGetResult()
    {
        $a = new StringAnalyser(self::DEFAULT_PLACEHOLDER_INPUT);

        $this->assertInstanceOf('Pvra\\AnalysisResult', $r = $a->getResult());
        $this->assertSame($r, $a->getResult());

        $this->assertNotEmpty($r->getAnalysisTargetId());

        $a = new StringAnalyser(self::DEFAULT_PLACEHOLDER_INPUT);

        $a->setResultInstance($r = new AnalysisResult());
        $this->assertSame($r, $a->getResult());
    }

    public function testAttachRequirementWalker()
    {
        $a = new StringAnalyser(self::DEFAULT_PLACEHOLDER_INPUT);
        $m = $this->getMock('Pvra\\AnalyserAwareInterface');
        $m->expects($this->once())
            ->method('setOwningAnalyser')
            ->with($this->equalTo($a));

        $a->attachRequirementVisitor($m);
    }

    public function testAttachRequirementWalkers()
    {
        $a = new StringAnalyser(self::DEFAULT_PLACEHOLDER_INPUT);
        $m = $this->getMock('Pvra\\AnalyserAwareInterface');
        $m->expects($this->exactly(2))
            ->method('setOwningAnalyser')
            ->with($this->equalTo($a));

        $a->attachRequirementVisitors([$m, $m]);
    }

    /**
     * @covers Pvra\Analyser::isAnalyserRun
     */
    public function testIsAnalyserRun()
    {
        $a = new StringAnalyser(self::DEFAULT_PLACEHOLDER_INPUT);
        $this->assertFalse($a->isAnalyserRun());
        $a->run();
        $this->assertTrue($a->isAnalyserRun());

        $result = new AnalysisResult();
        $a = new StringAnalyser(self::DEFAULT_PLACEHOLDER_INPUT);
        $a->setResultInstance($result);
        $this->assertFalse($a->isAnalyserRun());
        $this->assertSame(md5(self::DEFAULT_PLACEHOLDER_INPUT), $result->getAnalysisTargetId());
        $this->assertSame($result, $a->getResult());
        $result->seal();
        $this->assertTrue($a->isAnalyserRun());
        $this->assertSame($result, $a->run());
        $this->assertSame($result, $a->getResult());
    }

    public function testSetResultInstance()
    {
        $result = $this->getMock('Pvra\\AnalysisResult');
        $result->expects($this->once())->method('setAnalysisTargetId');

        $a = new StringAnalyser(self::DEFAULT_PLACEHOLDER_INPUT);
        $a->setResultInstance($result);
    }

    public function testHasNodeTraverser()
    {
        $a = new StringAnalyser(self::DEFAULT_PLACEHOLDER_INPUT, false);
        $this->assertFalse($a->hasNodeTraverserAttached());
        $this->assertInstanceOf('PhpParser\NodeTraverserInterface', $a->getNodeTraverser());
        $this->assertTrue($a->hasNodeTraverserAttached());
        $a = new StringAnalyser(self::DEFAULT_PLACEHOLDER_INPUT);
        $this->assertTrue($a->hasNodeTraverserAttached());
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage A result instance was already set. Overriding it may lead to data loss.
     */
    public function testSetResultInstanceException()
    {
        $a = new StringAnalyser(self::DEFAULT_PLACEHOLDER_INPUT);
        $a->run();
        $a->setResultInstance(new AnalysisResult());
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage The attached Result instance is already sealed.
     */
    public function testSetResultInstanceAlreadySealedException()
    {
        $a = new StringAnalyser(self::DEFAULT_PLACEHOLDER_INPUT);
        $res = new AnalysisResult();
        $res->seal();
        $a->setResultInstance($res);
    }
}
