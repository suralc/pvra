<?php

namespace Pvra\tests\Analysers;

use Pvra\Analysers\LibraryAdditions;
use Pvra\Result\Reason;
use Pvra\tests\BaseNodeWalkerTestCase;

class LibraryAdditionsTest extends BaseNodeWalkerTestCase
{
    protected $classToTest = 'Pvra\\Analysers\\LibraryAdditions';
    protected $expandNames = true;

    public function testMixedDetection()
    {
        $res = $this->runInstanceFromScratch('libraryAdditions');

        $expected = [
            [3, Reason::LIB_FUNCTION_ADDITION],
            [4, Reason::LIB_FUNCTION_ADDITION],
            [6, Reason::LIB_FUNCTION_ADDITION],
            [7, Reason::LIB_CLASS_ADDITION],
            [8, Reason::LIB_CLASS_ADDITION],
            [12, Reason::LIB_CLASS_ADDITION],
            [12, Reason::LIB_CLASS_ADDITION],
            [12, Reason::LIB_CLASS_ADDITION],
            [19, Reason::LIB_CLASS_ADDITION],
            [21, Reason::LIB_CLASS_ADDITION],
            [25, Reason::LIB_CLASS_ADDITION],
            [25, Reason::LIB_CLASS_ADDITION],
            [42, Reason::LIB_CLASS_ADDITION],
            [46, Reason::LIB_CLASS_ADDITION],
        ];

        $this->assertCount(count($expected) + /* 5.6 below the foreach */
            1, $res);

        foreach ($expected as $pos => $req) {
            $this->assertSame($req[0], $res->getRequirementInfo('5.4.0')[ $pos ]['line']);
            $this->assertSame($req[1], $res->getRequirementInfo('5.4.0')[ $pos ]['reason']);
        }

        $this->assertSame(4, $res->getRequirementInfo('5.6.0')[0]['line']);
        $this->assertSame(Reason::LIB_FUNCTION_ADDITION, $res->getRequirementInfo('5.6.0')[0]['reason']);
    }

    public function testPropertyOfNonObjectOnCountNamePartsInParameterTypeHint()
    {
        // this triggered a notice before the fix in 44f16c2bd9
        $result = $this->runInstanceFromScratch('libAdditionsPropOnNonObjInParamHint');
        $this->assertCount(0, $result);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testExpectedInvalidFileFormatException()
    {
        new LibraryAdditions(TEST_FILE_ROOT . '/invalidNonExistingLibrarySource.php');
    }

     /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The $information parameter has to be an instance of LibraryInformation, string or an array. SplFileInfo given.
     */
    public function testExpectedExceptionOnObjectParameterTypeOnConstruct()
    {
        new LibraryAdditions(new \SplFileInfo(TEST_FILE_ROOT . '/invalidNonExistingLibrarySource.php'));
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The $information parameter has to be an instance of LibraryInformation, string or an array. resource given.
     */
    public function testExpectedExceptionOnWrongParameterTypeOnConstruct()
    {
        new LibraryAdditions(fopen('php://memory', 'rw'));
    }
}
