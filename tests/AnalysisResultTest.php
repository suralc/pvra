<?php

namespace Pvra\tests;


use PHPUnit_Framework_TestCase;
use Pvra\AnalysisResult;
use Pvra\Result\MessageFormatter;
use Pvra\Result\Reason as R;

class AnalysisResultTest extends PHPUnit_Framework_TestCase
{

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Impossible to write to already sealed result
     */
    public function testAddArbRequirementWhileSealedException()
    {
        $r = new AnalysisResult();

        $r->seal();
        $r->addArbitraryRequirement('5.5.5');
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Impossible to write to already sealed result
     */
    public function testAddRequirementWhileSealedException()
    {
        $r = new AnalysisResult();

        $r->seal();
        $r->addArbitraryRequirement('5.5.5');
    }

    public function testAddRequirementUnreasonedException()
    {
        try {
            $r = new AnalysisResult();

            $r->addRequirement(R::LIB_CLASS_ADDITION);
            $this->fail('Unreachable statement reached. Exception not triggered');
        } catch (\LogicException $ex) {
            $this->assertStringMatchesFormat('%s::%s requires a reason a version can be associated to.'
                . ' Use %s::addArbitraryRequirement() to add any version with any reasoning to the result.',
                $ex->getMessage());
        } catch (\Exception $ex) {
            $this->fail('Wrong exceptiontype received');
        }
    }

    public function testIsSealed()
    {
        $r = new AnalysisResult();

        $this->assertFalse($r->isSealed());
        $r->seal();
        $this->assertTrue($r->isSealed());
    }

    public function testGetRequiredVersion()
    {
        $r = new AnalysisResult();
        $this->assertSame('5.3.0', $r->getRequiredVersion());
        $r->addArbitraryRequirement('5.5.5');
        $r->addArbitraryRequirement('5.4.3');
        $this->assertSame('5.5.5', $r->getRequiredVersion());
        $r->addArbitraryRequirement('5.5.5', [__FILE__ . ':' . __LINE__], 'Some msg');
        $this->assertSame('5.5.5', $r->getRequiredVersion());
        $r->addArbitraryRequirement('5.6.0', [__FILE__ . ':' . __LINE__], 'Some msg');
        $this->assertSame('5.6.0', $r->getRequiredVersion());
    }

    public function testGetRequiredVersionWithReasonedRequirements()
    {
        $r = new AnalysisResult();
        $r->addRequirement(R::ARRAY_FUNCTION_DEREFERENCING);
        $this->assertSame('5.4.0', $r->getRequiredVersion());
        $r->addRequirement(R::ARGUMENT_UNPACKING);
        $this->assertSame('5.6.0', $r->getRequiredVersion());
        $r->addRequirement(R::EXPR_IN_EMPTY);
        $this->assertSame('5.6.0', $r->getRequiredVersion());
        $this->assertCount(3, $r->getRequirements());
        $this->assertCount(1, $r->getRequirementInfo('5.4.0'));
        $this->assertCount(1, $r->getRequirementInfo('5.5.0'));
        $this->assertCount(1, $r->getRequirementInfo('5.6.0'));
    }

    public function testGetRequiredVersionWithMixedRequirementDefinition()
    {
        $r = new AnalysisResult();
        $r->addRequirement(R::EXPR_IN_EMPTY);
        $this->assertSame(R::getVersionFromReason(R::EXPR_IN_EMPTY), $r->getRequiredVersion());
        $r->addArbitraryRequirement('5.5.1');
        $this->assertSame('5.5.1', $r->getRequiredVersion());
        $r->addRequirement(R::VARIADIC_ARGUMENT);
        $this->assertSame('5.6.0', $r->getRequiredVersion());
        $r->addArbitraryRequirement('7.0.1', 544, 'Some msg', R::LIB_CLASS_ADDITION);
        $this->assertSame('7.0.1', $r->getRequiredVersion());
        $this->assertSame(544, $r->getRequirementInfo('7.0.1')[0]['line']);
        $this->assertSame('Some msg', $r->getRequirementInfo('7.0.1')[0]['msg']);
        $this->assertSame(R::LIB_CLASS_ADDITION, $r->getRequirementInfo('7.0.1')[0]['reason']);
        $r->addArbitraryRequirement('4.3.0');
        $r->addRequirement(R::ARGUMENT_UNPACKING);
        $this->assertSame('7.0.1', $r->getRequiredVersion());
    }

    public function testGetRequiredVersionId()
    {
        $r = new AnalysisResult();

        $r->addArbitraryRequirement('5.4.0');
        $this->assertSame(50400, $r->getRequiredVersionId());
        $r->addArbitraryRequirement('5.4.1');
        $this->assertSame(50401, $r->getRequiredVersionId());

        $r = new AnalysisResult();
        $r->addArbitraryRequirement('0.5.30');
        $this->assertSame(530, $r->getRequiredVersionId());

        $r->addArbitraryRequirement('5.5');
        $this->assertSame(50500, $r->getRequiredVersionId());

        $r->addArbitraryRequirement('205.12.989');
        $this->assertSame(2052189, $r->getRequiredVersionId());

        $r = new AnalysisResult();

        $r->addArbitraryRequirement(PHP_VERSION);
        $this->assertSame(PHP_VERSION_ID, $r->getRequiredVersionId());
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage A version id has to be built from two or three segments. "5" is not valid.
     */
    public function testGetRequiredVersionIdException()
    {
        $r = new AnalysisResult();
        $r->addArbitraryRequirement('5');

        $r->getRequiredVersionId();
    }

    public function testGetRequirementInfo()
    {
        $r = new AnalysisResult();
        $this->assertEmpty($r->getRequirementInfo('5.0.0'));

        $r->addArbitraryRequirement('5.0.1');
        $this->assertEmpty($r->getRequirementInfo('5.0.0'));
        $this->assertCount(1, $r->getRequirementInfo('5.0.1'));
    }

    public function testCount()
    {
        $r = new AnalysisResult();
        $this->assertSame(0, $r->count());
        $this->assertCount(0, $r);
        $this->assertTrue(count($r) === 0);
        $r->addArbitraryRequirement('5.9.3');
        $this->assertCount(1, $r);
        $r->addArbitraryRequirement('2.3.4');
        $this->assertCount(2, $r);
        $r->addRequirement(R::VARIADIC_ARGUMENT);
        $this->assertCount(3, $r);
        $r->addArbitraryLimit('5.6.7');
        $this->assertCount(4, $r);
    }

    public function testGetIterator()
    {
        $r = new AnalysisResult();

        $this->assertInstanceOf('\Traversable', $r);
        $this->assertInstanceOf('\Iterator', $it = $r->getIterator());

        $this->assertSame(0, $it->count());
        $this->assertSame($r->count(), $it->count());

        $r->addArbitraryRequirement('5.2.3');
        $this->assertSame(1, $r->getIterator()->count());
        $arr = $r->getIterator()->current();
        $this->assertTrue($arr['version'] === '5.2.3');
        $this->assertArrayHasKey('msg', $arr);
        $this->assertArrayHasKey('reason', $arr);

        $r = new AnalysisResult();
        $r->addArbitraryRequirement('5.5.0');
        $r->addArbitraryRequirement('5.5.0');
        $r->addArbitraryRequirement('5.5.0');
        $r->addArbitraryRequirement('5.4.0');

        $this->assertSame($r->count(), $r->getIterator()->count());
        $this->assertCount(4, $r->getIterator());
        foreach ($r as $item) {
            $this->assertArrayHasKey('version', $item);
            $this->assertArrayHasKey('msg', $item);
            $this->assertArrayHasKey('reason', $item);
            $this->assertTrue($item['version'] === '5.5.0' || $item['version'] === '5.4.0');
        }
    }

    public function testGetIteratorWithLimits()
    {
        $r = new AnalysisResult();
        $r->addArbitraryLimit('5.5.0');
        $this->assertCount(1, $r->getIterator());
        $r->addArbitraryLimit('5.5.0');
        $this->assertCount(2, $r->getIterator());
        $r->addArbitraryRequirement('5.5.0');
        $this->assertCount(3, $r->getIterator());
        /** @var \Pvra\Result\Reasoning $item */
        foreach($r as $item) {
            $this->assertInstanceOf('\Pvra\Result\Reasoning', $item);
            $this->assertSame('5.5.0', $item->get('version'));
        }
    }

    public function testSetAnalysisTargetId()
    {
        $r = new AnalysisResult();
        $this->assertSame(AnalysisResult::INITIAL_ANALYSIS_TARGET_ID, $r->getAnalysisTargetId());
        $r->setAnalysisTargetId('abc');
        $this->assertSame('abc', $r->getAnalysisTargetId());
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage You cannot modify an already set or sealed result.
     */
    public function testExceptionOnTargetIdOverride()
    {
        $r = new AnalysisResult();
        $r->setAnalysisTargetId('def');
        $this->assertSame('def', $r->getAnalysisTargetId());
        $r->setAnalysisTargetId('g')->setAnalysisTargetId('f');
        $this->assertNotSame('f', $r->getAnalysisTargetId());
    }

    public function testGetMessageFormatter()
    {
        $res = new AnalysisResult();
        $this->assertInstanceOf('\Pvra\Result\MessageFormatter', $res->getMsgFormatter());
    }

    public function testSetMessageFormatter()
    {
        $res = new AnalysisResult();
        $this->assertSame($res, $res->setMsgFormatter($f = new MessageFormatter()));
        $this->assertSame($f, $res->getMsgFormatter());
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage You cannot modify an already set or sealed result.
     */
    public function testSetAnalysisTargetIdSealedException()
    {
        $r = new AnalysisResult();
        $r->seal();
        $r->setAnalysisTargetId('new id');
    }

    public function testAddLimitSimple()
    {
        $r = new AnalysisResult();
        $r->addLimit(R::ARGUMENT_UNPACKING);
        $this->assertSame('5.6.0', $r->getLimitInfo('5.6.0')[0]->get('version'));
    }

    public function testGetLimitInfoOnEmptyLimits()
    {
        $r = new AnalysisResult();
        $this->assertSame([], $r->getLimits());
        $this->assertSame([], $r->getLimitInfo('5.6.0'));
    }

    public function testAddArbitraryLimit()
    {
        $r = new AnalysisResult();
        $this->assertSame($r, $r->addArbitraryLimit('5.5.0'));
        $this->assertCount(1, $r->getLimitInfo('5.5.0'));
        $this->assertSame('5.5.0', $r->getLimitInfo('5.5.0')[0]->get('version'));
        $r->addArbitraryLimit('5.6.0', 12, 'Hello', R::LIB_FUNCTION_REMOVAL, ['functionName' => 'abc']);
        $reasoning = $r->getLimitInfo('5.6.0')[0];
        $this->assertSame('5.6.0', $reasoning['version']);
        $this->assertSame(12, $reasoning['line']);
        $this->assertSame('Hello', $reasoning['raw_msg']);
        $this->assertSame(R::LIB_FUNCTION_REMOVAL, $reasoning['reason']);
        $this->assertSame(['functionName' => 'abc'], $reasoning['data']);
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage addArbitraryLimit() to add any version with any reasoning to the result.
     */
    public function testAddLimitInvalidReasonException()
    {
        $r = new AnalysisResult();
        $r->addLimit(R::LIB_CLASS_ADDITION);
    }
}
