<?php

namespace Pvra\tests\Analysers;


use Pvra\Result\Reason;
use Pvra\Result\Reasoning;
use Pvra\tests\BaseNodeWalkerTestCase;

class Php54FeaturesTest extends BaseNodeWalkerTestCase
{
    protected $classToTest = '\Pvra\Analysers\Php54Features';

    public function testClosureMixedExamples()
    {
        $expected = [
            [3, Reason::TYPEHINT_CALLABLE],
            [4, Reason::TYPEHINT_CALLABLE],
            [14, Reason::ARRAY_FUNCTION_DEREFERENCING],
            [14, Reason::THIS_IN_CLOSURE],
            [16, Reason::THIS_IN_CLOSURE],
            [16, Reason::TYPEHINT_CALLABLE],
            [17, Reason::THIS_IN_CLOSURE],
            [20, Reason::THIS_IN_CLOSURE],
        ];

        $this->runTestsAgainstExpectation($expected, '5.4/closures', '5.4.0');
    }

    public function testMixed54()
    {
        $expected = [
            [3, Reason::TRAIT_DEFINITION],
            [5, Reason::TYPEHINT_CALLABLE],
            [8, Reason::ARRAY_FUNCTION_DEREFERENCING],
            [8, Reason::THIS_IN_CLOSURE],
            [13, Reason::TRAIT_DEFINITION],
            [17, Reason::TRAIT_MAGIC_CONST],
            [22, Reason::TRAIT_MAGIC_CONST],
            [26, Reason::TRAIT_DEFINITION],
            [33, Reason::THIS_IN_CLOSURE],
            [40, Reason::TRAIT_USE],
            [41, Reason::TRAIT_USE],
            [55, Reason::ARRAY_FUNCTION_DEREFERENCING],
            [57, Reason::INSTANT_CLASS_MEMBER_ACCESS],
            [58, Reason::INSTANT_CLASS_MEMBER_ACCESS],
            [61, Reason::BINARY_NUMBER_DECLARATION],
            [64, Reason::SHORT_ARRAY_DECLARATION],
            [66, Reason::SHORT_ARRAY_DECLARATION],
            [72, Reason::SHORT_ARRAY_DECLARATION],
            [76, Reason::SHORT_ARRAY_DECLARATION],
            [85, Reason::STATIC_CALL_BY_EXPRESSION],
        ];

        $this->runTestsAgainstExpectation($expected, '5.4/all54', '5.4.0');
    }

    public function testShortEchoOpenDetection()
    {
        $result = $this->runInstanceFromScratch('5.4/short_echo_tags');

        $this->assertCount(1, $result);

        /** @var Reasoning $reasoning */
        $reasoning = $result->getIterator()->current();
        $this->assertSame('5.4.0', $reasoning['version']);
        $this->assertSame(1, $reasoning['line']);
        $this->assertSame(Reason::SHORT_ECHO_TAG, $reasoning['reason']);
    }
}
