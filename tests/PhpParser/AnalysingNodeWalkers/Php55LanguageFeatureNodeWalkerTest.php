<?php

namespace Pvra\tests\PhpParser\AnalysingNodeWalkers;


use Pvra\RequirementAnalysis\Result\RequirementReason as R;
use Pvra\tests\BaseNodeWalkerTestCase;

class Php55LanguageFeatureNodeWalkerTest extends BaseNodeWalkerTestCase
{
    protected $classToTest = 'Pvra\\PhpParser\AnalysingNodeWalkers\\Php55LanguageFeatureNodeWalker';

    public function testGeneratorDetection()
    {
        $expected = [
            [6, R::GENERATOR_DEFINITION],
            [7, R::GENERATOR_DEFINITION],
            [8, R::GENERATOR_DEFINITION],
            [10, R::GENERATOR_DEFINITION],
            [11, R::GENERATOR_DEFINITION],
            [13, R::GENERATOR_DEFINITION],
            [14, R::GENERATOR_DEFINITION],
            [17, R::GENERATOR_DEFINITION],
        ];

        $this->runTestsAgainstExpectation($expected, '5.5/generators', '5.5.0');
    }

    public function testFinallyDetection()
    {
        $expected = [
            [9, R::TRY_CATCH_FINALLY],
            [19, R::TRY_CATCH_FINALLY],
            [25, R::TRY_CATCH_FINALLY],
        ];

        $this->runTestsAgainstExpectation($expected, '5.5/finally', '5.5.0');
    }

    public function testMixedDetection()
    {
        $expected = [
            [10, R::TRY_CATCH_FINALLY],
            [11, R::LIST_IN_FOREACH],
            [12, R::EXPR_IN_EMPTY],
            [13, R::GENERATOR_DEFINITION],
            [13, R::ARRAY_OR_STRING_DEREFERENCING],
            [15, R::GENERATOR_DEFINITION],
            [15, R::ARRAY_OR_STRING_DEREFERENCING],
            [21, R::CLASS_NAME_RESOLUTION],
        ];

        $this->runTestsAgainstExpectation($expected, '5.5/all55', '5.5.0');
    }
}
