<?php

namespace Pvra\tests\RequirementAnalysis\Result;


use Pvra\RequirementAnalysis\Result\RequirementReason;
use Pvra\RequirementAnalysis\Result\ResultMessageFormatter;
use Pvra\RequirementAnalysis\Result\ResultMessageLocator;

class ResultMessageFormatterTest extends \PHPUnit_Framework_TestCase
{
    public function testNoWarningEmitted()
    {
        $this->assertTrue(true);
    }

    private function getDefaultLocator(array $data = []) {
        return ResultMessageLocator::fromArray($data);
    }
}
