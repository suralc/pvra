<?php

namespace Pvra\tests\PhpParser\AnalysingNodeWalkers;


use Pvra\RequirementAnalysis\Result\RequirementReason;
use Pvra\tests\BaseNodeWalkerTestCase;

class Php54LanguageFeatureNodeWalkerTest extends BaseNodeWalkerTestCase
{
    protected $classToTest = '\Pvra\PhpParser\AnalysingNodeWalkers\Php54LanguageFeatureNodeWalker';

    public function testClosureMixedExamples()
    {
        $res = $this->runInstanceFromScratch('closures');

        $this->assertSame('5.4.0', $res->getRequiredVersion());
        $expected = [
            [3, RequirementReason::TYPEHINT_CALLABLE],
            [4, RequirementReason::TYPEHINT_CALLABLE],
            [14, RequirementReason::ARRAY_FUNCTION_DEREFERENCING],
            [14, RequirementReason::THIS_IN_CLOSURE],
            [16, RequirementReason::THIS_IN_CLOSURE],
            [16, RequirementReason::TYPEHINT_CALLABLE],
            [17, RequirementReason::THIS_IN_CLOSURE],
            [20, RequirementReason::THIS_IN_CLOSURE],
        ];

        foreach ($expected as $key => $shouldBe) {
            $this->assertSame($shouldBe[0], $res->getRequirementInfo('5.4.0')[ $key ]['line']);
            $this->assertSame($shouldBe[1], $res->getRequirementInfo('5.4.0')[ $key ]['reason']);
        }
    }

    public function testMixed54()
    {
        $res = $this->runInstanceFromScratch('all54');

        $this->assertSame('5.4.0', $res->getRequiredVersion());

        $expected = [
            [3, RequirementReason::TRAIT_DEFINITION],
            [5, RequirementReason::TYPEHINT_CALLABLE],
            [8, RequirementReason::ARRAY_FUNCTION_DEREFERENCING],
            [8, RequirementReason::THIS_IN_CLOSURE],
            [13, RequirementReason::TRAIT_DEFINITION],
            [17, RequirementReason::TRAIT_MAGIC_CONST],
            [22, RequirementReason::TRAIT_MAGIC_CONST],
            [26, RequirementReason::TRAIT_DEFINITION],
            [33, RequirementReason::THIS_IN_CLOSURE],
            [40, RequirementReason::TRAIT_USE],
            [41, RequirementReason::TRAIT_USE],
            [55, RequirementReason::ARRAY_FUNCTION_DEREFERENCING],
            [57, RequirementReason::INSTANT_CLASS_MEMBER_ACCESS],
            [58, RequirementReason::INSTANT_CLASS_MEMBER_ACCESS],
            [61, RequirementReason::BINARY_NUMBER_DECLARATION],
            [64, RequirementReason::SHORT_ARRAY_DECLARATION],
            [66, RequirementReason::SHORT_ARRAY_DECLARATION],
            [72, RequirementReason::SHORT_ARRAY_DECLARATION],
            [76, RequirementReason::SHORT_ARRAY_DECLARATION],
        ];

        $this->assertCount(count($expected), $res->getRequirementInfo('5.4.0'),
            'Not all or too many elements were matched.');

        foreach ($expected as $key => $shouldBe) {
            $this->assertSame($shouldBe[0], $res->getRequirementInfo('5.4.0')[ $key ]['line']);
            $this->assertSame($shouldBe[1], $res->getRequirementInfo('5.4.0')[ $key ]['reason']);
        }
    }
}
