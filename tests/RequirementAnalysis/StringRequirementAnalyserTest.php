<?php

namespace Pvra\tests\Requirementanalysis;


use Pvra\RequirementAnalysis\StringRequirementAnalyser;

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

    public function testResultGeneration()
    {
        $a = new StringRequirementAnalyser(self::DEFAULT_PLACEHOLDER_INPUT);
    }

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
    }

    public function testAttachRequirementWalker()
    {
        $a = new StringRequirementAnalyser(self::DEFAULT_PLACEHOLDER_INPUT);
        $m = $this->getMock('Pvra\\PhpParser\\RequirementAnalyserAwareInterface');
        $m->expects($this->once())->
            method('setOwningAnalyser')->with($this->equalTo($a));

        $a->attachRequirementAnalyser($m);
    }
}
