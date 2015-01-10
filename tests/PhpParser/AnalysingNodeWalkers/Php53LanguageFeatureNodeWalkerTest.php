<?php
namespace Pvra\tests\PhpParser\AnalysingNodeWalkers;


use Pvra\RequirementAnalysis\Result\RequirementReason as R;
use Pvra\RequirementAnalysis\Result\RequirementReason;
use Pvra\tests\BaseNodeWalkerTestCase;

/**
 * Class Php53LanguageFeatureNodeWalkerTest
 *
 * There are more tests for 5.3 features as modifications on the lexer were required.
 *
 * @package Pvra\tests\PhpParser\AnalysingNodeWalkers
 */
class Php53LanguageFeatureNodeWalkerTest extends BaseNodeWalkerTestCase
{
    protected $classToTest = 'Pvra\\PhpParser\\AnalysingNodeWalkers\\Php53LanguageFeatureNodeWalker';

    public function testNamespaceDetection()
    {
        $result = $this->runInstanceFromScratch('5.3/namespaces');

        $expected = [
            [2, R::NAMESPACE_DECLERATION],
            [3, R::NAMESPACE_MAGIC_CONSTANT],
            [6, R::NAMESPACE_DECLERATION],
            [7, R::NAMESPACE_MAGIC_CONSTANT],
            [11, R::NAMESPACE_DECLERATION],
            [12, R::NAMESPACE_IMPORT]
        ];

        $this->assertCount(count($expected), $result);

        foreach ($expected as $num => $expectation) {
            $this->assertSame($expectation[0], $result->getRequirementInfo('5.3.0')[ $num ]['line']);
            $this->assertSame($expectation[1], $result->getRequirementInfo('5.3.0')[ $num ]['reason']);
        }
    }

    public function testFileLevelNamespaceDetection()
    {
        $result = $this->runInstanceFromScratch('5.3/file_level_namespace');

        $expected = [
            [2, R::NAMESPACE_DECLERATION],
            [4, R::NAMESPACE_IMPORT],
            [6, R::NAMESPACE_MAGIC_CONSTANT]
        ];

        $this->assertCount(count($expected), $result);

        foreach ($expected as $num => $expectation) {
            $this->assertSame($expectation[0], $result->getRequirementInfo('5.3.0')[ $num ]['line']);
            $this->assertSame($expectation[1], $result->getRequirementInfo('5.3.0')[ $num ]['reason']);
        }
    }

    public function testGotoDetection()
    {
        $result = $this->runInstanceFromScratch('5.3/goto');

        $expected = [
            [6, R::GOTO_KEYWORD],
            [11, R::JUMP_LABEL],
        ];

        $this->assertCount(count($expected), $result);

        foreach ($expected as $num => $expectation) {
            $this->assertSame($expectation[0], $result->getRequirementInfo('5.3.0')[ $num ]['line']);
            $this->assertSame($expectation[1], $result->getRequirementInfo('5.3.0')[ $num ]['reason']);
        }
    }

    public function testNowDocDetection()
    {
        $result = $this->runInstanceFromScratch('5.3/nowdoc');

        $expected = [
            [8, R::NOWDOC_LITERAL],
            [15, R::NOWDOC_LITERAL],
        ];

        $this->assertCount(count($expected), $result);

        foreach ($expected as $num => $expectation) {
            $this->assertSame($expectation[0], $result->getRequirementInfo('5.3.0')[ $num ]['line']);
            $this->assertSame($expectation[1], $result->getRequirementInfo('5.3.0')[ $num ]['reason']);
        }
    }

    public function testMagicMethodDetection()
    {
        $result = $this->runInstanceFromScratch('5.3/magic');

        $expected = [
            [5, RequirementReason::INVOKE_MAGIC_METHOD],
            [10, RequirementReason::CALLSTATIC_MAGIC_METHOD],
            [17, RequirementReason::INVOKE_MAGIC_METHOD],
            [18, RequirementReason::CALLSTATIC_MAGIC_METHOD]
        ];

        $this->assertCount(count($expected), $result);

        foreach ($expected as $num => $expectation) {
            $this->assertSame($expectation[0], $result->getRequirementInfo('5.3.0')[ $num ]['line']);
            $this->assertSame($expectation[1], $result->getRequirementInfo('5.3.0')[ $num ]['reason']);
        }
    }

    public function testDocFormatConstantDetection()
    {
        $result = $this->runInstanceFromScratch('5.3/docFormatConstant');

        $expected = [
            [6, RequirementReason::CONST_KEYWORD_DOC_SYNTAX],
            [6, RequirementReason::NOWDOC_LITERAL],
            [9, RequirementReason::CONST_KEYWORD_DOC_SYNTAX],
            [12, RequirementReason::CONST_KEYWORD_DOC_SYNTAX],
        ];

        $this->assertCount(count($expected), $result);

        foreach ($expected as $num => $expectation) {
            $this->assertSame($expectation[0], $result->getRequirementInfo('5.3.0')[ $num ]['line']);
            $this->assertSame($expectation[1], $result->getRequirementInfo('5.3.0')[ $num ]['reason']);
        }
    }

    public function testConstOutsideClass()
    {
        $result = $this->runInstanceFromScratch('5.3/constOutsideClass');

        $expected = [
            [3, RequirementReason::CONST_KEYWORD_OUTSIDE_CLASS],
            [4, RequirementReason::CONST_KEYWORD_OUTSIDE_CLASS],
            [4, RequirementReason::CONST_KEYWORD_DOC_SYNTAX],
        ];

        $this->assertCount(count($expected), $result);

        foreach ($expected as $num => $expectation) {
            $this->assertSame($expectation[0], $result->getRequirementInfo('5.3.0')[ $num ]['line']);
            $this->assertSame($expectation[1], $result->getRequirementInfo('5.3.0')[ $num ]['reason']);
        }
    }

    public function testShortTernaryDetection()
    {
        $result = $this->runInstanceFromScratch('5.3/ternary');

        $expected = [
            [4, RequirementReason::SHORT_TERNARY],
            [5, RequirementReason::SHORT_TERNARY],
            [5, RequirementReason::SHORT_TERNARY],
        ];

        $this->assertCount(count($expected), $result);

        foreach ($expected as $num => $expectation) {
            $this->assertSame($expectation[0], $result->getRequirementInfo('5.3.0')[ $num ]['line']);
            $this->assertSame($expectation[1], $result->getRequirementInfo('5.3.0')[ $num ]['reason']);
        }
    }

    public function testLsbAndStaticByExpressionDetection()
    {
        $result = $this->runInstanceFromScratch('5.3/lsb');

        $expected = [
            [3, RequirementReason::NAMESPACE_DECLERATION],
            [18, RequirementReason::LATE_STATE_BINDING_USING_STATIC],
            [19, RequirementReason::LATE_STATE_BINDING_USING_STATIC],
            [20, RequirementReason::LATE_STATE_BINDING_USING_STATIC],
            [22, RequirementReason::DYNAMIC_ACCESS_TO_STATIC],
            [26, RequirementReason::LATE_STATE_BINDING_USING_STATIC],
        ];

        $this->assertCount(count($expected), $result);

        foreach ($expected as $num => $expectation) {
            $this->assertSame($expectation[0], $result->getRequirementInfo('5.3.0')[ $num ]['line']);
            $this->assertSame($expectation[1], $result->getRequirementInfo('5.3.0')[ $num ]['reason']);
        }
    }

    /**
     * @covers Pvra\PhpParser\AnalysingNodeWalkers\Php53LanguageFeatureNodeWalker::detectNamespaceSeparator
     */
    public function testNamespaceSeparatorDetection()
    {
        $this->markTestIncomplete('Refactoring of Library Walker(s) required.');
        $result = $this->runInstanceFromScratch('5.3/separator');

        $expected = [
            [3, RequirementReason::NAMESPACE_DECLERATION],
            [5, RequirementReason::NAMESPACE_IMPORT],
            [6, RequirementReason::NAMESPACE_IMPORT],
            [8, RequirementReason::NAMESPACE_SEPARATOR],
            [9, RequirementReason::NAMESPACE_SEPARATOR],
            [10, RequirementReason::NAMESPACE_SEPARATOR],
            [11, RequirementReason::NAMESPACE_SEPARATOR],
            [12, RequirementReason::NAMESPACE_SEPARATOR],
            [13, RequirementReason::NAMESPACE_SEPARATOR],
        ];

        $this->assertCount(count($expected), $result);

        foreach ($expected as $num => $expectation) {
            $this->assertSame($expectation[0], $result->getRequirementInfo('5.3.0')[ $num ]['line']);
            $this->assertSame($expectation[1], $result->getRequirementInfo('5.3.0')[ $num ]['reason']);
        }
    }

    public function testAll53()
    {
        $result = $this->runInstanceFromScratch('5.3/all53');

        $expected = [
            [3, RequirementReason::NAMESPACE_DECLERATION],
            [5, RequirementReason::NAMESPACE_IMPORT],
            [12, RequirementReason::CONST_KEYWORD_DOC_SYNTAX],
            [19, RequirementReason::JUMP_LABEL],
            [20, RequirementReason::LATE_STATE_BINDING_USING_STATIC],
            [21, RequirementReason::NAMESPACE_MAGIC_CONSTANT],
            [22, RequirementReason::DYNAMIC_ACCESS_TO_STATIC],
            [23, RequirementReason::LATE_STATE_BINDING_USING_STATIC],
            [24, RequirementReason::GOTO_KEYWORD],
            [26, RequirementReason::LATE_STATE_BINDING_USING_STATIC],
            [26, RequirementReason::NOWDOC_LITERAL],
            [31, RequirementReason::CALLSTATIC_MAGIC_METHOD],
            [36, RequirementReason::INVOKE_MAGIC_METHOD],
            [38, RequirementReason::CLOSURE_DECLARATION],
            [45, RequirementReason::NAMESPACE_DECLERATION],
            [46, RequirementReason::NAMESPACE_IMPORT],
            [48, RequirementReason::CONST_KEYWORD_OUTSIDE_CLASS],
            [50, RequirementReason::CALLSTATIC_MAGIC_METHOD],
            [52, RequirementReason::INVOKE_MAGIC_METHOD],
            [53, RequirementReason::SHORT_TERNARY],
        ];

        $this->assertCount(count($expected), $result);

        foreach ($expected as $num => $expectation) {
            $this->assertSame($expectation[0], $result->getRequirementInfo('5.3.0')[ $num ]['line']);
            $this->assertSame($expectation[1], $result->getRequirementInfo('5.3.0')[ $num ]['reason']);
        }
    }
}
