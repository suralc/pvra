<?php

namespace Pvra\tests\Result;


use Mockery as m;
use Pvra\AnalysisResult;
use Pvra\Result\Collection;
use Pvra\Result\Reason;

class CollectionTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @param $id
     * @param $version
     * @return \Mockery\MockInterface|\Pvra\AnalysisResult
     */
    private function createResultMock($id, $version)
    {
        $mock = m::mock('Pvra\AnalysisResult')->makePartial();
        /** @var \Mockery\MockInterface|\Pvra\AnalysisResult $mock */
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
        $r = new Collection();
        $this->assertFalse($r->has('abc'));
        $this->assertFalse($r->has($result = new AnalysisResult()));
        $r->add($result);
        $this->assertTrue($r->has($result));
        $resultMock = m::mock('Pvra\AnalysisResult')->makePartial();
        // called by add and has
        $resultMock->shouldReceive('getAnalysisTargetId')->andReturn('my_id');
        $r->add($resultMock);
        $this->assertTrue($r->has($resultMock));
        $this->assertTrue($r->has('my_id'));
    }

    public function testInvalidKeyType()
    {
        $r = new Collection();
        $this->assertFalse($r->has([new Collection()]));
    }

    public function testAddByCount()
    {
        $r = new Collection();
        $result1 = new AnalysisResult();
        $result1->setAnalysisTargetId('id1');
        $this->assertCount(0, $r);
        $r->add($result1);
        $this->assertCount(1, $r);
        $r->add($result1);
        $this->assertCount(1, $r);
        $result2 = new AnalysisResult();
        $result2->setAnalysisTargetId('id1');
        $r->add($result2);
        $this->assertCount(1, $r);
        $result3 = new AnalysisResult();
        $result3->setAnalysisTargetId('is2');
        $r->add($result3);
        $this->assertCount(2, $r);
    }

    public function testGetHighestDemandingResult()
    {
        $r = new Collection();
        $ar = $this->createResultMock('1', '5.3.0');
        $r->add($ar);
        $this->assertSame('5.3.0', $r->getHighestDemandingResult()->getRequiredVersion());
        $ar = $this->createResultMock('2', '5.3.1');
        $r->add($ar);
        $this->assertSame($ar, $r->getHighestDemandingResult());
        $this->assertSame('5.3.1', $r->getHighestDemandingResult()->getRequiredVersion());
        $r = new Collection();
        $this->assertNull($r->getHighestDemandingResult());
        // ensure that newer, but lower requirement doesn't override the old one
        $r->add($this->createResultMock('1', Reason::TYPEHINT_CALLABLE));
        $r->add($this->createResultMock('2', Reason::LIST_IN_FOREACH));
        $r->add($this->createResultMock('3', Reason::GOTO_KEYWORD));
        $this->assertSame('5.5.0', $r->getHighestDemandingResult()->getRequiredVersion());
    }

    public function testJsonSerialization()
    {
        $r = new Collection();
        $ar = new AnalysisResult();
        $ar->setAnalysisTargetId('my_id');
        $ar->addRequirement(Reason::ARGUMENT_UNPACKING);
        $r->add($ar);
        $arr = json_decode(json_encode($r), true);
        $this->assertArrayHasKey('my_id', $arr);
        $this->assertCount(1, $arr['my_id']);
        $this->assertSame(Reason::ARGUMENT_UNPACKING, $arr['my_id'][0]['reason']);
    }

    public function testRemove()
    {
        $r = new Collection();
        $ar1 = new AnalysisResult();
        $ar1->setAnalysisTargetId('ar1');
        $ar1->addArbitraryRequirement('5.5.0');
        $ar2 = new AnalysisResult();
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
        $r = new Collection();
        $r->remove(12);
    }
}
