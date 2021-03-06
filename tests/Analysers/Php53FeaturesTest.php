<?php
namespace Pvra\tests\Analysers;


use Pvra\Analysers\Php53Features;
use Pvra\Result\Reason;
use Pvra\Result\Reason as R;
use Pvra\StringAnalyser;
use Pvra\tests\BaseNodeWalkerTestCase;

/**
 * Class Php53LanguageFeatureNodeWalkerTest
 *
 * There are more tests for 5.3 features as modifications on the lexer were required.
 *
 * @package Pvra\tests\PhpParser\Analysers
 */
class Php53FeaturesTest extends BaseNodeWalkerTestCase
{
    protected $classToTest = 'Pvra\\Analysers\\Php53Features';

    public function testNamespaceDetection()
    {
        $expected = [
            [2, R::NAMESPACE_DECLERATION],
            [3, R::NAMESPACE_MAGIC_CONSTANT],
            [6, R::NAMESPACE_DECLERATION],
            [7, R::NAMESPACE_MAGIC_CONSTANT],
            [11, R::NAMESPACE_DECLERATION],
            [12, R::NAMESPACE_IMPORT]
        ];

        $this->runTestsAgainstExpectation($expected, '5.3/namespaces', '5.3.0');
    }

    public function testFileLevelNamespaceDetection()
    {
        $expected = [
            [2, R::NAMESPACE_DECLERATION],
            [4, R::NAMESPACE_IMPORT],
            [6, R::NAMESPACE_MAGIC_CONSTANT]
        ];

        $this->runTestsAgainstExpectation($expected, '5.3/file_level_namespace');
    }

    public function testGotoDetection()
    {
        $expected = [
            [6, R::GOTO_KEYWORD],
            [11, R::JUMP_LABEL],
        ];

        $this->runTestsAgainstExpectation($expected, '5.3/goto', '5.3.0');
    }

    public function testNowDocDetection()
    {
        $expected = [
            [8, R::NOWDOC_LITERAL],
            [15, R::NOWDOC_LITERAL],
        ];

        $this->runTestsAgainstExpectation($expected, '5.3/nowdoc', '5.3.0');
    }

    public function testMagicMethodDetection()
    {
        $expected = [
            [5, Reason::INVOKE_MAGIC_METHOD],
            [10, Reason::CALLSTATIC_MAGIC_METHOD],
            [17, Reason::INVOKE_MAGIC_METHOD],
            [18, Reason::CALLSTATIC_MAGIC_METHOD]
        ];
        $this->runTestsAgainstExpectation($expected, '5.3/magic', '5.3.0');
    }

    public function testDocFormatConstantDetection()
    {
        $expected = [
            [6, Reason::CONST_KEYWORD_DOC_SYNTAX],
            [6, Reason::NOWDOC_LITERAL],
            [9, Reason::CONST_KEYWORD_DOC_SYNTAX],
            [12, Reason::CONST_KEYWORD_DOC_SYNTAX],
        ];

        $this->runTestsAgainstExpectation($expected, '5.3/docFormatConstant', '5.3.0');
    }

    public function testConstOutsideClass()
    {
        $expected = [
            [3, Reason::CONST_KEYWORD_OUTSIDE_CLASS],
            [4, Reason::CONST_KEYWORD_OUTSIDE_CLASS],
            [4, Reason::CONST_KEYWORD_DOC_SYNTAX],
        ];
        $this->runTestsAgainstExpectation($expected, '5.3/constOutsideClass', '5.3.0');
    }

    public function testShortTernaryDetection()
    {
        $expected = [
            [4, Reason::SHORT_TERNARY],
            [5, Reason::SHORT_TERNARY],
            [5, Reason::SHORT_TERNARY],
        ];

        $this->runTestsAgainstExpectation($expected, '5.3/ternary', '5.3.0');
    }

    public function testLsbAndStaticByExpressionDetection()
    {
        $expected = [
            [3, Reason::NAMESPACE_DECLERATION],
            [18, Reason::LATE_STATE_BINDING_USING_STATIC],
            [19, Reason::LATE_STATE_BINDING_USING_STATIC],
            [20, Reason::LATE_STATE_BINDING_USING_STATIC],
            [22, Reason::DYNAMIC_ACCESS_TO_STATIC],
            [26, Reason::LATE_STATE_BINDING_USING_STATIC],
        ];

        $this->runTestsAgainstExpectation($expected, '5.3/lsb', '5.3.0');
    }

    public function testAll53()
    {
        $expected = [
            [3, Reason::NAMESPACE_DECLERATION],
            [5, Reason::NAMESPACE_IMPORT],
            [12, Reason::CONST_KEYWORD_DOC_SYNTAX],
            [19, Reason::JUMP_LABEL],
            [20, Reason::LATE_STATE_BINDING_USING_STATIC],
            [21, Reason::NAMESPACE_MAGIC_CONSTANT],
            [22, Reason::DYNAMIC_ACCESS_TO_STATIC],
            [23, Reason::LATE_STATE_BINDING_USING_STATIC],
            [24, Reason::GOTO_KEYWORD],
            [26, Reason::LATE_STATE_BINDING_USING_STATIC],
            [26, Reason::NOWDOC_LITERAL],
            [31, Reason::CALLSTATIC_MAGIC_METHOD],
            [36, Reason::INVOKE_MAGIC_METHOD],
            [38, Reason::CLOSURE_DECLARATION],
            [45, Reason::NAMESPACE_DECLERATION],
            [46, Reason::NAMESPACE_IMPORT],
            [48, Reason::CONST_KEYWORD_OUTSIDE_CLASS],
            [50, Reason::CALLSTATIC_MAGIC_METHOD],
            [52, Reason::INVOKE_MAGIC_METHOD],
            [53, Reason::SHORT_TERNARY],
            [55, Reason::DIR_MAGIC_CONSTANT],
        ];

        $this->runTestsAgainstExpectation($expected, '5.3/all53', '5.3.0');
    }

    public function testAll53WithoutModeAddition()
    {
        $this->runTestsAgainstExpectation([], '5.3/all53', null,
            Php53Features::MODE_ALL & ~Php53Features::MODE_ADDITION);
    }

    public function testBaseConstructorAnalyserInjection()
    {
        $analyser = new StringAnalyser('<?php echo "hello world";');
        $fa = new Php53Features([], $analyser);
        $this->assertSame($analyser, $fa->getOwningAnalyser());
    }

    public function testNewByRefDetectionWithoutDeprMode()
    {
        $this->runTestsAgainstExpectation([], '5.3/new_by_ref', null,
            Php53Features::MODE_ALL & ~Php53Features::MODE_DEPRECATION);
    }

    public function testNewByRefDetectionWithDeprMode()
    {
        $expected = [
            [3, Reason::NEW_ASSIGN_BY_REF_DEP],
            [7, Reason::NEW_ASSIGN_BY_REF_DEP],
        ];

        $this->runTestsAgainstExpectation($expected, '5.3/new_by_ref', '-5.3.0', Php53Features::MODE_DEPRECATION);
    }
}
