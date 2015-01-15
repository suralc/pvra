<?php

namespace Pvra\tests\RequirementAnalysis\Result;


use Mockery as m;
use Pvra\RequirementAnalysis\RequirementAnalysisResult;
use Pvra\RequirementAnalysis\Result\RequirementReason;
use Pvra\RequirementAnalysis\Result\ResultCollection;

class ResultCollectionTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @param $id
     * @param $version
     * @return \Mockery\MockInterface|\Pvra\RequirementAnalysis\RequirementAnalysisResult
     */
    private function createResultMock($id, $version)
    {
        $mock = m::mock('Pvra\RequirementAnalysis\RequirementAnalysisResult')->makePartial();
        /** @var \Mockery\MockInterface|\Pvra\RequirementAnalysis\RequirementAnalysisResult $mock */
        $mock->shouldReceive('getAnalysisTargetId')->andReturn($id);
        if(is_int($version)) {
            $mock->addRequirement($version);
        } else {
            $mock->addArbitraryRequirement($version);
        }

        return $mock;
    }
    public function tearDown()
    {
        \Mockery::close();
    }

    public function testHas()
    {
        $r = new ResultCollection();
        $this->assertFalse($r->has('abc'));
        $this->assertFalse($r->has($result = new RequirementAnalysisResult()));
        $r->add($result);
        $this->assertTrue($r->has($result));
        $resultMock = m::mock('Pvra\RequirementAnalysis\RequirementAnalysisResult')->makePartial();
        // called by add and has
        $resultMock->shouldReceive('getAnalysisTargetId')->andReturn('my_id');
        $r->add($resultMock);
        $this->assertTrue($r->has($resultMock));
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage "array" is not a valid keytype in a ResultCollection
     */
    public function testHasException()
    {
        $r = new ResultCollection();
        $r->has([new ResultCollection()]);
    }

    public function testAddByCount()
    {
        $r = new ResultCollection();
        $result1 = new RequirementAnalysisResult();
        $result1->setAnalysisTargetId('id1');
        $this->assertCount(0, $r);
        $r->add($result1);
        $this->assertCount(1, $r);
        $r->add($result1);
        $this->assertCount(1, $r);
        $result2 = new RequirementAnalysisResult();
        $result2->setAnalysisTargetId('id1');
        $r->add($result2);
        $this->assertCount(1, $r);
        $result3 = new RequirementAnalysisResult();
        $result3->setAnalysisTargetId('is2');
        $r->add($result3);
        $this->assertCount(2, $r);
    }

    public function testGetHighestDemandingResult()
    {
        $r = new ResultCollection();
        $ar = $this->createResultMock('1', '5.3.0');
        $r->add($ar);
        $this->assertSame('5.3.0', $r->getHighestDemandingResult()->getRequiredVersion());
        $ar = $this->createResultMock('2', '5.3.1');
        $r->add($ar);
        $this->assertSame($ar, $r->getHighestDemandingResult());
        $this->assertSame('5.3.1', $r->getHighestDemandingResult()->getRequiredVersion());
        $r = new ResultCollection();
        $this->assertNull($r->getHighestDemandingResult());
        // ensure that newer, but lower requirement doesn't override the old one
        $r->add($this->createResultMock('1', RequirementReason::TYPEHINT_CALLABLE));
        $r->add($this->createResultMock('2', RequirementReason::LIST_IN_FOREACH));
        $r->add($this->createResultMock('3', RequirementReason::GOTO_KEYWORD));
        $this->assertSame('5.5.0', $r->getHighestDemandingResult()->getRequiredVersion());
    }

    public function testJsonSerialization()
    {
        $r = new ResultCollection();
        $ar = new RequirementAnalysisResult();
        $ar->setAnalysisTargetId('my_id');
        $ar->addRequirement(RequirementReason::ARGUMENT_UNPACKING);
        $r->add($ar);
        $arr = json_decode(json_encode($r), true);
        $this->assertArrayHasKey('my_id', $arr);
        $this->assertCount(1, $arr['my_id']);
        $this->assertSame(RequirementReason::ARGUMENT_UNPACKING, $arr['my_id'][0]['reason']);
    }

    public function testRemove()
    {
        $r = new ResultCollection();
        $ar1 = new RequirementAnalysisResult();
        $ar1->setAnalysisTargetId('ar1');
        $ar1->addArbitraryRequirement('5.5.0');
        $ar2 = new RequirementAnalysisResult();
        $ar2->setAnalysisTargetId('ar2');
        $ar2->addArbitraryRequirement('5.5.2');
        $r->add($ar1);
        $r->add($ar2);
        $this->assertCount(2, $r);
        $this->assertSame($ar2, $r->getHighestDemandingResult());
        $this->assertTrue($r->remove($ar2));
        $this->assertCount(1, $r);
        $this->assertSame($ar1, $r->getHighestDemandingResult());
        $this->assertTrue($r->remove($ar1));
        $this->assertCount(0, $r);
        $this->assertSame(null, $r->getHighestDemandingResult());
        $r->add($ar2);
        $r->add($ar1);
        $r->remove('ar1');
        $this->assertSame($ar2, $r->getHighestDemandingResult());
        $this->assertCount(1, $r);
        $this->assertTrue($r->remove('non-existant'));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testInvalidTypeOnRemove()
    {
        $r = new ResultCollection();
        $r->remove(12);
    }
}
