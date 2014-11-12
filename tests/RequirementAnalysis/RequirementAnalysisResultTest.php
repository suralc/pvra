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
}
