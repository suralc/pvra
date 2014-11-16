<?php

namespace Pvra\tests\PhpParser\AnalysingNodeWalkers;


use Pvra\RequirementAnalysis\Result\RequirementReason as R;
use Pvra\tests\BaseNodeWalkerTestCase;

class Php55LanguageFeatureNodeWalkerTest extends BaseNodeWalkerTestCase
{
    protected $classToTest = 'Pvra\\PhpParser\AnalysingNodeWalkers\\Php55LanguageFeatureNodeWalker';

    public function testGeneratorDetection()
    {
        $res = $this->runInstanceFromScratch('generators');

        $this->assertSame('5.5.0', $res->getRequiredVersion());
        $this->assertCount(1, $res->getRequirements());
        $this->assertCount(8, $res->getRequirementInfo('5.5.0'));
        $lines = [6, 7, 8, 10, 11, 13, 14, 17];
        $match = 0;
        foreach ($res->getRequirementInfo('5.5.0') as $req) {
            $this->assertSame($lines[ $match ], $req['location']['line']);
            $this->assertSame(R::GENERATOR_DEFINITION, $req['category']);
            $match++;
        }
    }

    public function testFinallyDetection()
    {
        $res = $this->runInstanceFromScratch('finally');

        $this->assertSame('5.5.0', $res->getRequiredVersion());
        $this->assertCount(1, $res->getRequirements());
        $this->assertCount(3, $res->getRequirementInfo('5.5.0'));

        $this->assertSame(9, $res->getRequirementInfo('5.5.0')[0]['location']['line']);
        $this->assertSame(19, $res->getRequirementInfo('5.5.0')[1]['location']['line']);
        $this->assertSame(25, $res->getRequirementInfo('5.5.0')[2]['location']['line']);

        $this->assertSame(R::TRY_CATCH_FINALLY, $res->getRequirementInfo('5.5.0')[0]['category']);
        $this->assertSame(R::TRY_CATCH_FINALLY, $res->getRequirementInfo('5.5.0')[1]['category']);
        $this->assertSame(R::TRY_CATCH_FINALLY, $res->getRequirementInfo('5.5.0')[2]['category']);
    }

    public function testMixedDetection()
    {
        $res = $this->runInstanceFromScratch('all55');

        $this->assertSame('5.5.0', $res->getRequiredVersion());
        $this->assertCount(8, $res->getRequirementInfo('5.5.0'));
        $this->assertCount(1, $res->getRequirements());
        $expectations = [
            [10, R::TRY_CATCH_FINALLY],
            [11, R::LIST_IN_FOREACH],
            [12, R::EXPR_IN_EMPTY],
            [13, R::GENERATOR_DEFINITION],
            [13, R::ARRAY_OR_STRING_DEREFERENCING],
            [15, R::GENERATOR_DEFINITION],
            [15, R::ARRAY_OR_STRING_DEREFERENCING],
            [21, R::CLASS_NAME_RESOLUTION],
        ];

        foreach($expectations as $num => $expectation) {
            $this->assertSame($expectation[0], $res->getRequirementInfo('5.5.0')[$num]['location']['line']);
            $this->assertSame($expectation[1], $res->getRequirementInfo('5.5.0')[$num]['category']);
        }

    }
}
