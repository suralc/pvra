<?php

namespace Pvra\tests\RequirementAnalysis;


use PHPUnit_Framework_TestCase;
use Pvra\RequirementAnalysis\RequirementAnalysisResult;

class RequirementAnalysisResultTest extends PHPUnit_Framework_TestCase
{

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Impossible to write to already sealed result
     */
    public function testAddRequirementWhileSealedException()
    {
        $r = new RequirementAnalysisResult();

        $r->seal();
        $r->addArbitraryRequirement('5.5.5');
    }

    public function testIsSealed()
    {
        $r = new RequirementAnalysisResult();

        $this->assertFalse($r->isSealed());
        $r->seal();
        $this->assertTrue($r->isSealed());
    }

    public function testGetRequiredVersion()
    {
        $r = new RequirementAnalysisResult();
        $this->assertSame('5.3.0', $r->getRequiredVersion());
        $r->addArbitraryRequirement('5.5.5');
        $r->addArbitraryRequirement('5.4.3');
        $this->assertSame('5.5.5', $r->getRequiredVersion());
        $r->addArbitraryRequirement('5.5.5', [__FILE__ . ':' . __LINE__], 'Some msg');
        $this->assertSame('5.5.5', $r->getRequiredVersion());
        $r->addArbitraryRequirement('5.6.0', [__FILE__ . ':' . __LINE__], 'Some msg');
        $this->assertSame('5.6.0', $r->getRequiredVersion());
    }

    public function testGetRequiredVersionId()
    {
        $r = new RequirementAnalysisResult();

        $r->addArbitraryRequirement('5.4.0');
        $this->assertSame(50400, $r->getRequiredVersionId());
        $r->addArbitraryRequirement('5.4.1');
        $this->assertSame(50401, $r->getRequiredVersionId());

        $r = new RequirementAnalysisResult();
        $r->addArbitraryRequirement('0.5.30');
        $this->assertSame(530, $r->getRequiredVersionId());

        $r->addArbitraryRequirement('5.5');
        $this->assertSame(50500, $r->getRequiredVersionId());

        $r->addArbitraryRequirement('205.12.989');
        $this->assertSame(2052189, $r->getRequiredVersionId());

        $r = new RequirementAnalysisResult();

        $r->addArbitraryRequirement(PHP_VERSION);
        $this->assertSame(PHP_VERSION_ID, $r->getRequiredVersionId());
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage A version id has to be built from two or three segments. "5" is not valid.
     */
    public function testGetRequiredVersionIdException()
    {
        $r = new RequirementAnalysisResult();
        $r->addArbitraryRequirement('5');

        $a = $r->getRequiredVersionId();
    }

    public function testGetRequirementInfo()
    {
        $r = new RequirementAnalysisResult();
        $this->assertEmpty($r->getRequirementInfo('5.0.0'));

        $r->addArbitraryRequirement('5.0.1');
        $this->assertEmpty($r->getRequirementInfo('5.0.0'));
        $this->assertCount(1, $r->getRequirementInfo('5.0.1'));
    }
}
