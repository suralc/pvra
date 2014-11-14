<?php

namespace Pvra\tests\RequirementAnalysis;


use Pvra\RequirementAnalysis\RequirementAnalysisResult;
use Pvra\RequirementAnalysis\StringRequirementAnalyser;
use \Mockery as m;

/**
 * Class StringRequirementAnalyserTest
 *
 * These tests cover the abstract base class.
 *
 * @package Pvra\tests\Requirementanalysis
 */
class StringRequirementAnalyserTest extends \PHPUnit_Framework_TestCase
{
    const DEFAULT_PLACEHOLDER_INPUT = '<?php trait abc {}';

    public function testInit()
    {
        $a = new StringRequirementAnalyser(self::DEFAULT_PLACEHOLDER_INPUT);
        $this->assertInstanceOf('PhpParser\\Parser', $a->getParser());
        $this->assertInstanceOf('PhpParser\\NodeTraverser', $a->getNodeTraverser());
    }

    public function testGetResult()
    {
        $a = new StringRequirementAnalyser(self::DEFAULT_PLACEHOLDER_INPUT);

        $this->assertInstanceOf('Pvra\\RequirementAnalysis\\RequirementAnalysisResult', $r = $a->getResult());
        $this->assertSame($r, $a->getResult());

        $this->assertNotEmpty($r->getAnalysisTargetId());

        $a = new StringRequirementAnalyser(self::DEFAULT_PLACEHOLDER_INPUT);

        $a->setResultInstance($r = new RequirementAnalysisResult());
        $this->assertSame($r, $a->getResult());
    }

    public function testAttachRequirementWalker()
    {
        $a = new StringRequirementAnalyser(self::DEFAULT_PLACEHOLDER_INPUT);
        $m = $this->getMock('Pvra\\PhpParser\\RequirementAnalyserAwareInterface');
        $m->expects($this->once())->
            method('setOwningAnalyser')->with($this->equalTo($a));

        $a->attachRequirementAnalyser($m);
    }

    /**
     * @covers Pvra\RequirementAnalysis\RequirementAnalyser::isAnalyserRun
     */
    public function testIsAnalyserRun()
    {
        $a = new StringRequirementAnalyser(self::DEFAULT_PLACEHOLDER_INPUT);
        $this->assertFalse($a->isAnalyserRun());
        $a->run();
        $this->assertTrue($a->isAnalyserRun());

        $result = new RequirementAnalysisResult();
        $a = new StringRequirementAnalyser(self::DEFAULT_PLACEHOLDER_INPUT);
        $a->setResultInstance($result);
        $this->assertFalse($a->isAnalyserRun());
        $this->assertSame(md5(self::DEFAULT_PLACEHOLDER_INPUT), $result->getAnalysisTargetId());
        $this->assertSame($result, $a->getResult());
        $result->seal();
        $this->assertTrue($a->isAnalyserRun());

    }

    public function testSetResultInstance()
    {
        $result = $this->getMock('Pvra\\RequirementAnalysis\\RequirementAnalysisResult');
        $result->expects($this->once())->method('setAnalysisTargetId');

        $a = new StringRequirementAnalyser(self::DEFAULT_PLACEHOLDER_INPUT);
        $a->setResultInstance($result);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage A result instance was already set. Overriding it may lead to data loss.
     */
    public function testSetResultInstanceException()
    {
        $a = new StringRequirementAnalyser(self::DEFAULT_PLACEHOLDER_INPUT);
        $a->run();
        $a->setResultInstance(new RequirementAnalysisResult());
    }
}
