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
        $keys = ['data', 'reason', 'reasonName', 'line', 'msg', 'raw_msg', 'version', 'targetId'];
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

    public function testToArray()
    {
        $reason = $this->createDefaultRequirementReasoningInstance();
        $reasonArray = $reason->toArray();
        foreach(['data', 'reason', 'reasonName', 'line', 'msg', 'raw_msg', 'version', 'targetId'] as $key) {
            $this->assertArrayHasKey($key, $reasonArray);
            $this->assertSame($reason[$key], $reasonArray[$key]);
        }
    }

    public function testJsonSerialization()
    {
        $reason = $this->createDefaultRequirementReasoningInstance();
        $this->assertSame(json_encode($reason->toArray()), $value = json_encode($reason));
        $value = json_decode($value, true);
        $this->assertSame($reason['msg'], $value['msg']);
        $this->assertSame($reason['reasonName'], $value['reasonName']); // check the calculated values
        $this->assertSame($reason['line'], $value['line']);
    }

    public function testGet()
    {
        $reason = $this->createDefaultRequirementReasoningInstance();
        $this->assertSame(12, $reason->get('reason'));
        $this->assertSame(54, $reason->get('line'));
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Pvra\RequirementAnalysis\Result\RequirementReasoning::$wrong accessed through "get" does not exist or is not accessible.
     */
    public function testGetException()
    {
        $reason = $this->createDefaultRequirementReasoningInstance();
        $reason->get('wrong');
    }

    private function createDefaultRequirementReasoningInstance()
    {
        return new RequirementReasoning(12, 54, new RequirementAnalysisResult(), '5.4.2', 'my msg');
    }
}
