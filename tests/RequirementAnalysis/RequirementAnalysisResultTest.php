<?php

namespace Pvra\tests\Requirementanalysis;


use PHPUnit_Framework_TestCase;
use Pvra\RequirementAnalysis\RequirementAnalysisResult;

class RequirementAnalysisResultTest extends PHPUnit_Framework_TestCase
{
    public function testGetRequiredVersion()
    {
        $r = new RequirementAnalysisResult();
        $this->assertSame('5.3.0', $r->getRequiredVersion());
        $r->addRequirement('5.5.5');
        $r->addRequirement('5.4.3');
        $this->assertSame('5.5.5', $r->getRequiredVersion());
        $r->addRequirement('5.5.5', [__FILE__ . ':' . __LINE__], 'Some msg');
        $this->assertSame('5.5.5', $r->getRequiredVersion());
        $r->addRequirement('5.6.0', [__FILE__ . ':' . __LINE__], 'Some msg');
        $this->assertSame('5.6.0', $r->getRequiredVersion());
    }

    public function testGetRequiredVersionInt()
    {
        $r = new RequirementAnalysisResult();

        $this->assertSame('5.3.0', $r->getRequiredVersion());
        $this->assertSame(50300, $r->getRequiredVersionInt());

        $r->addRequirement('5.30.0');
        $this->assertSame('5.30.0', $r->getRequiredVersion());
        $this->assertSame(503000, $r->getRequiredVersionInt());

        $r = new RequirementAnalysisResult();
        $r->addRequirement('0.5.30');

        $this->assertSame(50300, $r->getRequiredVersionInt());

        $r->addRequirement(PHP_VERSION);
        $this->assertSame(PHP_VERSION_ID, $r->getRequiredVersionInt());
    }
}
