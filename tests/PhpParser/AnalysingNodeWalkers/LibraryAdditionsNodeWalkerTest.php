<?php

namespace Pvra\tests\PhpParser\AnalysingNodeWalkers;

use Pvra\RequirementAnalysis\Result\RequirementReason;
use Pvra\tests\BaseNodeWalkerTestCase;

class LibraryAdditionsNodeWalkerTest extends BaseNodeWalkerTestCase
{
    protected $classToTest = 'Pvra\\PhpParser\\AnalysingNodeWalkers\\LibraryAdditionsNodeWalker';

    public function testMixedDetection()
    {
        $res = $this->runInstanceFromScratch('libraryAdditions');

        $expected = [
            [3, RequirementReason::FUNCTION_PRESENCE_CHANGE],
            [4, RequirementReason::FUNCTION_PRESENCE_CHANGE],
            [6, RequirementReason::FUNCTION_PRESENCE_CHANGE],
            [7, RequirementReason::CLASS_PRESENCE_CHANGE],
            [8, RequirementReason::CLASS_PRESENCE_CHANGE],
            [12, RequirementReason::CLASS_PRESENCE_CHANGE],
            [12, RequirementReason::CLASS_PRESENCE_CHANGE],
            [12, RequirementReason::CLASS_PRESENCE_CHANGE],
            [20, RequirementReason::CLASS_PRESENCE_CHANGE],
            [22, RequirementReason::CLASS_PRESENCE_CHANGE],
            [26, RequirementReason::CLASS_PRESENCE_CHANGE],
            [26, RequirementReason::CLASS_PRESENCE_CHANGE],
        ];

        $this->assertCount(12 + /* 5.6 below the foreach */
            1, $res);

        foreach ($expected as $pos => $req) {
            $this->assertSame($req[0], $res->getRequirementInfo('5.4.0')[ $pos ]['line']);
            $this->assertSame($req[1], $res->getRequirementInfo('5.4.0')[ $pos ]['reason']);
        }

        $this->assertSame(4, $res->getRequirementInfo('5.6.0')[0]['line']);
        $this->assertSame(RequirementReason::FUNCTION_PRESENCE_CHANGE, $res->getRequirementInfo('5.6.0')[0]['reason']);
    }
}
