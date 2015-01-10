<?php

namespace Pvra\tests\PhpParser\AnalysingNodeWalkers;


use Pvra\RequirementAnalysis\Result\RequirementReason as R;
use Pvra\tests\BaseNodeWalkerTestCase;


class Php56LanguageFeatureNodeWalkerTest extends BaseNodeWalkerTestCase
{
    protected $classToTest = 'Pvra\\PhpParser\AnalysingNodeWalkers\\Php56LanguageFeatureNodeWalker';

    public function testVariadics()
    {
        $res = $this->runInstanceFromScratch('5.6/variadics');
        $this->assertSame('5.6.0', $res->getRequiredVersion());

        $expected = [
            [4, R::VARIADIC_ARGUMENT],
            [8, R::VARIADIC_ARGUMENT],
            [13, R::VARIADIC_ARGUMENT],
            [15, R::VARIADIC_ARGUMENT],
            [20, R::VARIADIC_ARGUMENT],
        ];

        $this->assertCount(count($expected), $res->getRequirementInfo('5.6.0'));

        foreach ($expected as $num => $expectation) {
            $this->assertSame($expectation[0], $res->getRequirementInfo('5.6.0')[ $num ]['line']);
            $this->assertSame($expectation[1], $res->getRequirementInfo('5.6.0')[ $num ]['reason']);
        }
    }

    public function testMixedDetection()
    {
        $r = $this->runInstanceFromScratch('5.6/all56');
        $this->assertSame('5.6.0', $r->getRequiredVersion());

        $expected = [
            [5, R::CONSTANT_IMPORT_USE],
            [6, R::FUNCTION_IMPORT_USE],
            [8, R::VARIADIC_ARGUMENT],
            [10, R::ARGUMENT_UNPACKING],
            [16, R::CONSTANT_SCALAR_EXPRESSION],
            [17, R::CONSTANT_SCALAR_EXPRESSION],
            [19, R::VARIADIC_ARGUMENT],
            [21, R::VARIADIC_ARGUMENT],
            [26, R::VARIADIC_ARGUMENT],
            [27, R::POW_OPERATOR],
            [28, R::POW_OPERATOR],
            [29, R::POW_OPERATOR],
            [30, R::POW_OPERATOR],
            [31, R::POW_OPERATOR],
            [31, R::POW_OPERATOR],
            [36, R::ARGUMENT_UNPACKING],
            [37, R::ARGUMENT_UNPACKING],
        ];

        $this->assertCount(count($expected), $r->getRequirementInfo('5.6.0'));

        foreach ($expected as $num => $expectation) {
            $this->assertSame($expectation[0], $r->getRequirementInfo('5.6.0')[ $num ]['line']);
            $this->assertSame($expectation[1], $r->getRequirementInfo('5.6.0')[ $num ]['reason']);
        }
    }

    public function testConstantExpressionDetection()
    {
        $r = $this->runInstanceFromScratch('5.6/constantExpressions');

        $expected = [
            [4, R::CONSTANT_SCALAR_EXPRESSION],
            [5, R::CONSTANT_SCALAR_EXPRESSION],
            [6, R::CONSTANT_SCALAR_EXPRESSION],
            [11, R::CONSTANT_SCALAR_EXPRESSION],
            [12, R::CONSTANT_SCALAR_EXPRESSION],
            [13, R::CONSTANT_SCALAR_EXPRESSION],
        ];

        $this->assertCount(count($expected), $r->getRequirementInfo('5.6.0'));

        foreach ($expected as $num => $expectation) {
            $this->assertSame($expectation[0], $r->getRequirementInfo('5.6.0')[ $num ]['line']);
            $this->assertSame($expectation[1], $r->getRequirementInfo('5.6.0')[ $num ]['reason']);
        }
    }
}
