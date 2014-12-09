<?php

namespace Pvra\tests\RequirementAnalysis\Result;


use Mockery as m;
use Pvra\RequirementAnalysis\RequirementAnalysisResult;
use Pvra\RequirementAnalysis\Result\ResultCollection;

class ResultCollectionTest extends \PHPUnit_Framework_TestCase
{
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
}
