<?php

namespace Pvra\tests\RequirementAnalysis\Result;


use Pvra\RequirementAnalysis\RequirementAnalysisResult;
use Pvra\RequirementAnalysis\Result\RequirementReason;
use Pvra\RequirementAnalysis\Result\RequirementReasoning;

class RequirementReasoningTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \Exception
     */
    public function testOffsetSet()
    {
        $t = $this->createDefaultRequirementReasoningIntance();
        $t['version'] = 'new';
    }

    /**
     * @expectedException \Exception
     */
    public function testOffsetUnset()
    {
        $t = $this->createDefaultRequirementReasoningIntance();
        unset($t['version']);
    }

    public function testOffsetExists()
    {
        $keys = ['data', 'reason', 'reasonName', 'line', 'msg', 'version'];
        $t = $this->createDefaultRequirementReasoningIntance();
        foreach ($keys as $key) {
            $this->assertTrue(isset($t[ $key ]));
        }
    }

    public function testOffsetGetSimpleValue()
    {
        $t = new RequirementReasoning(12, 54, '5.4.2', new RequirementAnalysisResult(), 'my msg', ['data1' => 'abc']);
        $this->assertSame(12, $t['reason']);
        $this->assertSame(RequirementReason::getReasonNameFromValue(12), $t['reasonName']);
        $this->assertSame(54, $t['line']);
        $this->assertSame(['data1' => 'abc'], $t['data']);
        $this->assertSame('5.4.2', $t['version']);
        $this->assertSame('my msg', $t['msg']);
        $this->assertNull($t['none']);
    }

    public function testOffsetGetWithMessageFormatting()
    {
        $reason = new RequirementReasoning(12, 54, '5.4.2', new RequirementAnalysisResult());
        $this->assertTrue(strpos($reason['msg'], 'magic constant') !== false);
        $this->assertTrue(strpos($reason['msg'], '5.4.2') !== false);
        $this->assertTrue(strpos($reason['msg'], '54') !== false);
    }

    private function createDefaultRequirementReasoningIntance()
    {
        return new RequirementReasoning(12, 54, '5.4.2', new RequirementAnalysisResult(), 'my msg');
    }
}
