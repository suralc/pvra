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
            [3, Reason::FUNCTION_PRESENCE_CHANGE],
            [4, Reason::FUNCTION_PRESENCE_CHANGE],
            [6, Reason::FUNCTION_PRESENCE_CHANGE],
            [7, Reason::CLASS_PRESENCE_CHANGE],
            [8, Reason::CLASS_PRESENCE_CHANGE],
            [12, Reason::CLASS_PRESENCE_CHANGE],
            [12, Reason::CLASS_PRESENCE_CHANGE],
            [12, Reason::CLASS_PRESENCE_CHANGE],
            [19, Reason::CLASS_PRESENCE_CHANGE],
            [21, Reason::CLASS_PRESENCE_CHANGE],
            [25, Reason::CLASS_PRESENCE_CHANGE],
            [25, Reason::CLASS_PRESENCE_CHANGE],
        ];

        $this->assertCount(count($expected) + /* 5.6 below the foreach */
            1, $res);

        foreach ($expected as $pos => $req) {
            $this->assertSame($req[0], $res->getRequirementInfo('5.4.0')[ $pos ]['line']);
            $this->assertSame($req[1], $res->getRequirementInfo('5.4.0')[ $pos ]['reason']);
        }

        $this->assertSame(4, $res->getRequirementInfo('5.6.0')[0]['line']);
        $this->assertSame(Reason::FUNCTION_PRESENCE_CHANGE, $res->getRequirementInfo('5.6.0')[0]['reason']);
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
        $walker = new LibraryAdditions(null, TEST_FILE_ROOT . '/invalidNonExistingLibrarySource.php');
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Valid library data must have a "classes-added" list.
     */
    public function testExpectedClassKeyMissingException()
    {
        $walker = new LibraryAdditions(null, ['functions-added' => []]);
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Valid library data must have a "classes-added" list.
     */
    public function testExpectedClassKeyListMissingException()
    {
        $walker = new LibraryAdditions(null, ['functions-added' => [], 'classes-added' => '']);
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Valid library data must have a "functions-added" list.
     */
    public function testExpectedFunctionKeyMissingException()
    {
        $walker = new LibraryAdditions(null, ['classes-added' => []]);
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Valid library data must have a "functions-added" list.
     */
    public function testExpectedFunctionKeyListMissingException()
    {
        $walker = new LibraryAdditions(null, ['classes-added' => [], 'functions-added' => '']);
    }

    /**
     * @expectedExceptionMessage No valid, non-empty library information has been loaded. This should have happened in
     *     the constructor.
     * @expectedException \LogicException
     */
    public function testExceptionOnEmptyData()
    {
        $walker = new LibraryAdditions(null, []);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The $libraryData parameter has to be a string or an array. SplFileInfo given.
     */
    public function testExpectedExceptionOnObjectParameterTypeOnConstruct()
    {
        $walker = new LibraryAdditions(null,
            new \SplFileInfo(TEST_FILE_ROOT . '/invalidNonExistingLibrarySource.php'));
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The $libraryData parameter has to be a string or an array. resource given.
     */
    public function testExpectedExceptionOnWrongParameterTypeOnConstruct()
    {
        $walker = new LibraryAdditions(null,
            fopen('php://memory', 'rw'));
    }
}
