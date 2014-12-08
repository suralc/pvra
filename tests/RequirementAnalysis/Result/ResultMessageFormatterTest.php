<?php

namespace Pvra\tests\RequirementAnalysis\Result;


use Pvra\RequirementAnalysis\Result\RequirementReason;
use Pvra\RequirementAnalysis\Result\ResultMessageFormatter;

class ResultMessageFormatterTest extends \PHPUnit_Framework_TestCase
{
    public function testNoWarningEmitted()
    {
        $this->assertTrue(true);
    }
}
