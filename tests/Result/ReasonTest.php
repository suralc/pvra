<?php

namespace Pvra\tests\Result;


use Pvra\Result\Reason as R;

class ReasonTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        // make sure the class is clean, might've been used by another test class
        R::clear();
    }

    protected function tearDown()
    {
        R::clear();
    }

    public function testGetReasonNameFromValue()
    {
        $this->assertSame('TRAIT_USE', R::getReasonNameFromValue(R::TRAIT_USE));
        $this->assertSame('UNKNOWN', R::getReasonNameFromValue(989898));
        $this->assertSame('UNKNOWN', R::getReasonNameFromValue(R::UNKNOWN));
        $this->assertSame('LIB_FUNCTION_ADDITION', R::getReasonNameFromValue(R::LIB_FUNCTION_ADDITION));
    }

    public function testGetReasonNames()
    {
        // test regeneration
        $this->assertSame(R::getReasonNames(), $names = R::getReasonNames());
        $this->assertCount(57, $names);
        $this->assertArrayHasKey('UNKNOWN', $names);
        R::clear();
        $this->assertSame($names, R::getReasonNames());
        $this->assertSame(R::UNKNOWN, $names['UNKNOWN']);
        $this->assertSame(R::TRAIT_USE, $names['TRAIT_USE']);
        // ensure that each constant value is unique
        $this->assertCount(count(array_unique($names)),  $names);
    }

    public function testGetVersionFromReason()
    {
        $this->assertStringStartsWith('5.4', R::getVersionFromReason(R::ARRAY_FUNCTION_DEREFERENCING));
        $this->assertStringStartsWith('5.6', R::getVersionFromReason(R::ARGUMENT_UNPACKING));
        R::clear();
        // make sure values are reinitialized
        $this->assertTrue(is_string(R::getVersionFromReason(R::CONSTANT_IMPORT_USE)));
        $this->assertFalse(R::getVersionFromReason(R::LIB_FUNCTION_ADDITION));
        $this->assertFalse(R::getVersionFromReason(R::LIB_CLASS_ADDITION));
    }

    public function testGetVersionFromReasonException()
    {

        $tests = [
            R::CONSTANT_IMPORT_USE => false,
            R::LIB_CLASS_ADDITION => false,
            8748 => true,
            -24 => true,
            'some string' => true,
        ];

        foreach ($tests as $const => $trigger) {
            $triggered = false;
            try {
                R::getVersionFromReason($const);
            } catch (\InvalidArgumentException $ex) {
                $triggered = true;
                if ($trigger === false) {
                    $this->fail(sprintf("Unexpected %s for value %s with message: \n\t'%s'", get_class($ex), $const,
                        $ex->getMessage()));
                } else {
                    $this->assertStringMatchesFormat('There is no required version defined for this reason(id: "%s").',
                        $ex->getMessage());
                }
            }

            if ($triggered !== $trigger) {
                $this->fail('Expected exception not triggered');
            }
        }
    }
}
