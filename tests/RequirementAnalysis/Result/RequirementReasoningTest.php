<?php

namespace Pvra\tests\RequirementAnalysis\Result;


use Pvra\RequirementAnalysis\RequirementAnalysisResult;
use Pvra\RequirementAnalysis\Result\RequirementReason;
use Pvra\RequirementAnalysis\Result\RequirementReasoning;
use Pvra\RequirementAnalysis\Result\ResultMessageFormatter;
use Pvra\RequirementAnalysis\Result\ResultMessageLocator;

class RequirementReasoningTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \Exception
     */
    public function testOffsetSet()
    {
        $t = $this->createDefaultRequirementReasoningInstance();
        $t['version'] = 'new';
    }

    /**
     * @expectedException \Exception
     */
    public function testOffsetUnset()
    {
        $t = $this->createDefaultRequirementReasoningInstance();
        unset($t['version']);
    }

    public function testOffsetExists()
    {
        $keys = ['data', 'reason', 'reasonName', 'line', 'msg', 'raw_msg', 'version'];
        $t = $this->createDefaultRequirementReasoningInstance();
        foreach ($keys as $key) {
            $this->assertTrue(isset($t[ $key ]));
        }
    }

    public function testOffsetGetSimpleValue()
    {
        $t = new RequirementReasoning(RequirementReason::TRAIT_MAGIC_CONST, 54, (new RequirementAnalysisResult())
            ->setMsgFormatter(new ResultMessageFormatter(ResultMessageLocator::fromArray([
                RequirementReason::TRAIT_MAGIC_CONST => 'My required version is :version:'
            ]))), '5.4.2', null, ['data1' => 'abc']);
        $this->assertSame(12, $t['reason']);
        $this->assertSame(RequirementReason::getReasonNameFromValue(RequirementReason::TRAIT_MAGIC_CONST),
            $t['reasonName']);
        $this->assertSame(54, $t['line']);
        $this->assertSame(['data1' => 'abc'], $t['data']);
        $this->assertSame('5.4.2', $t['version']);
        $this->assertStringMatchesFormat('My required version is 5.4.2 in %s:%d', $t['msg']);
        $this->assertInternalType('string', $t['raw_msg']);
        $this->assertSame('My required version is :version:', $t['raw_msg']);
        $this->assertNull($t['none']);
    }

    public function testOffsetGetWithMessageFormatting()
    {
        $reason = new RequirementReasoning(12, 54, new RequirementAnalysisResult(), '5.4.2');
        $this->assertTrue(strpos($reason['msg'], 'magic constant') !== false);
        $this->assertTrue(strpos($reason['msg'], '5.4.2') !== false);
        $this->assertTrue(strpos($reason['msg'], '54') !== false);
    }

    private function createDefaultRequirementReasoningInstance()
    {
        return new RequirementReasoning(12, 54, new RequirementAnalysisResult(), '5.4.2', 'my msg');
    }
}
