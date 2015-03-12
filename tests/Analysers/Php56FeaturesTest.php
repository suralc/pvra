<?php

namespace Pvra\tests\Analysers;


use PhpParser\Node;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\ClassConst;
use Pvra\Analysers\Php56Features;
use Pvra\AnalysisResult;
use Pvra\Result\Reason as R;
use Pvra\tests\BaseNodeWalkerTestCase;


class Php56FeaturesTest extends BaseNodeWalkerTestCase
{
    protected $classToTest = 'Pvra\\Analysers\\Php56Features';

    public function testVariadics()
    {
        $expected = [
            [4, R::VARIADIC_ARGUMENT],
            [8, R::VARIADIC_ARGUMENT],
            [13, R::VARIADIC_ARGUMENT],
            [15, R::VARIADIC_ARGUMENT],
            [20, R::VARIADIC_ARGUMENT],
        ];

        $this->runTestsAgainstExpectation($expected, '5.6/variadics', '5.6.0');
    }

    public function testMixedDetection()
    {
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

        $this->runTestsAgainstExpectation($expected, '5.6/all56', '5.6.0');
    }

    public function testConstantExpressionDetection()
    {
        $expected = [
            [4, R::CONSTANT_SCALAR_EXPRESSION],
            [5, R::CONSTANT_SCALAR_EXPRESSION],
            [6, R::CONSTANT_SCALAR_EXPRESSION],
            [11, R::CONSTANT_SCALAR_EXPRESSION],
            [12, R::CONSTANT_SCALAR_EXPRESSION],
            [13, R::CONSTANT_SCALAR_EXPRESSION],
        ];

        $this->runTestsAgainstExpectation($expected, '5.6/constantExpressions', '5.6.0');
    }

    public function testConstantFetchIsNotMarkedAsUnsupportedBelow56()
    {
        $analyserMock = \Mockery::mock('\Pvra\StringAnalyser');
        $result = new AnalysisResult();
        $analyserMock->shouldReceive('getResult')->andReturn($result);
        $analyser = new Php56Features();
        $analyser->setOwningAnalyser($analyserMock);
        $analyser->enterNode(new ClassConst([
            new Node\Const_('STR', new LNumber()),
            new Node\Const_('DEF', new ConstFetch(new Name('abc'))),
            new Node\Const_('CONSTANTS', new ClassConstFetch(new Name('abc'), 'myConst'))
        ]));
        $this->assertCount(0, $result);
    }
}
