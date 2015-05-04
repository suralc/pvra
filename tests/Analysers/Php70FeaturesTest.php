<?php

namespace Pvra\tests\Analysers;


use Pvra\Analysers\Php70Features;
use Pvra\Result\Reason as R;
use Pvra\tests\BaseNodeWalkerTestCase;

class Php70FeaturesTest extends BaseNodeWalkerTestCase
{
    protected $classToTest = 'Pvra\Analysers\Php70Features';
    protected $expandNames = true;

    public function testReturnType()
    {
        $expected = [
            [4, R::RETURN_TYPE],
            [5, R::RETURN_TYPE],
            [6, R::RETURN_TYPE],
            [14, R::RETURN_TYPE],
            [17, R::RETURN_TYPE],
            [18, R::RETURN_TYPE],
            [25, R::RETURN_TYPE],
        ];

        $this->runTestsAgainstExpectation($expected, '7.0/return_type', '7.0.0');
    }

    public function testConstructorDeprecation()
    {
        $expected = [
            [6, R::PHP4_CONSTRUCTOR],
            [13, R::PHP4_CONSTRUCTOR],
            [20, R::PHP4_CONSTRUCTOR],
        ];

        $this->runTestsAgainstExpectation($expected, '7.0/php4_ctor', '-7.0.0', Php70Features::MODE_ALL);
    }

    /**
     * @dataProvider nonDeprecationFlagProvider
     */
    public function testPhp4ConstructorsAreNotMarkedWithoutDeprecationFlag($mode)
    {
        $expected = [];

        $this->runTestsAgainstExpectation($expected, '7.0/php4_ctor', null, $mode);
    }

    public function testReservedNamesDetection()
    {
        $expected = [
            [4, R::RESERVED_CLASS_NAME],
            [5, R::RESERVED_CLASS_NAME],
            [6, R::RESERVED_CLASS_NAME],
            [7, R::RESERVED_CLASS_NAME],
            [9, R::RESERVED_CLASS_NAME],
            [10, R::RESERVED_CLASS_NAME],
            [11, R::RESERVED_CLASS_NAME],
            [12, R::RESERVED_CLASS_NAME],
            [14, R::RESERVED_CLASS_NAME],
            [15, R::RESERVED_CLASS_NAME],
            [16, R::RESERVED_CLASS_NAME],
            [17, R::RESERVED_CLASS_NAME],
            [25, R::RESERVED_CLASS_NAME],
            [28, R::RESERVED_CLASS_NAME],
            [32, R::RESERVED_CLASS_NAME],
            [36, R::RESERVED_CLASS_NAME],
            [40, R::RESERVED_CLASS_NAME],
            [46, R::RESERVED_CLASS_NAME],
            [50, R::RESERVED_CLASS_NAME],
            [54, R::RESERVED_CLASS_NAME],
            [58, R::RESERVED_CLASS_NAME],
            [64, R::RESERVED_CLASS_NAME],
            [68, R::RESERVED_CLASS_NAME],
            [72, R::RESERVED_CLASS_NAME],
            [76, R::RESERVED_CLASS_NAME],
            [82, R::RESERVED_CLASS_NAME],
            [90, R::RESERVED_CLASS_NAME],
            [94, R::RESERVED_CLASS_NAME],
            [98, R::RESERVED_CLASS_NAME],
            [104, R::SOFT_RESERVED_NAME],
            [108, R::SOFT_RESERVED_NAME],
            [112, R::SOFT_RESERVED_NAME],
            [116, R::SOFT_RESERVED_NAME],
            [122, R::SOFT_RESERVED_NAME, ['fqn' => '\\SoftReserve\\Bool\\Resource', 'class' => 'Resource']],
            [133, R::SOFT_RESERVED_NAME, ['fqn' => 'Object', 'class' => 'Object']],
            [134, R::RESERVED_CLASS_NAME, ['fqn' => 'String', 'class' => 'String']],
            [135, R::RESERVED_CLASS_NAME, ['fqn' => 'FinallyOutOfNames\\Bool', 'class' => 'Bool']],
            [137, R::RESERVED_CLASS_NAME, ['fqn' => 'True', 'class' => 'True']],
            [138, R::RESERVED_CLASS_NAME, ['fqn' => 'False', 'class' => 'False']],
        ];
        $this->runTestsAgainstExpectation($expected, '7.0/reserved_names', '-7.0.0', Php70Features::MODE_ALL);
    }

    public function testReservedNamesAreNotMarkedWithoutDeprecationOrRemovalFlag()
    {
        $expected = [];

        $this->runTestsAgainstExpectation($expected, '7.0/reserved_names', null, Php70Features::MODE_ADDITION);
    }

    public function testOperatorDetection()
    {
        $expected = [
            [6, R::SPACESHIP_OPERATOR],
            [11, R::SPACESHIP_OPERATOR],
            [12, R::SPACESHIP_OPERATOR],
            [13, R::SPACESHIP_OPERATOR],
            [17, R::SPACESHIP_OPERATOR],
            [20, R::COALESCE_OPERATOR],
            [21, R::COALESCE_OPERATOR],
            [21, R::COALESCE_OPERATOR],
        ];

        $this->runTestsAgainstExpectation($expected, '7.0/operators', '7.0.0');
    }

    public function testAll70Additions()
    {
        $expected = [
            [11, R::RETURN_TYPE],
            [13, R::COALESCE_OPERATOR],
            [14, R::COALESCE_OPERATOR],
            [15, R::SPACESHIP_OPERATOR],
            [42, R::YIELD_FROM],
            [52, R::YIELD_FROM],
        ];

        $this->runTestsAgainstExpectation($expected, '7.0/all70', '7.0.0', Php70Features::MODE_ADDITION);
    }

    public function testAll70RemovalAndDeprecation()
    {
        $expected = [
            [4, R::RESERVED_CLASS_NAME],
            [19, R::RESERVED_CLASS_NAME],
            [25, R::PHP4_CONSTRUCTOR],
        ];

        $this->runTestsAgainstExpectation($expected, '7.0/all70', '-7.0.0',
            Php70Features::MODE_REMOVAL | Php70Features::MODE_DEPRECATION);
    }

    public function nonDeprecationFlagProvider()
    {
        return [
            [Php70Features::MODE_ADDITION],
            [Php70Features::MODE_REMOVAL],
            [Php70Features::MODE_ALL & ~Php70Features::MODE_DEPRECATION],
            [Php70Features::MODE_REMOVAL | Php70Features::MODE_ADDITION]
        ];
    }

    public function nonRemovalFlagProvider()
    {
        return [
            [Php70Features::MODE_ADDITION],
            [Php70Features::MODE_DEPRECATION],
            [Php70Features::MODE_ALL & ~Php70Features::MODE_REMOVAL],
            [Php70Features::MODE_DEPRECATION | Php70Features::MODE_ADDITION]
        ];
    }
}
