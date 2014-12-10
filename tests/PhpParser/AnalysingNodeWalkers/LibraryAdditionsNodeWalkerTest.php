<?php

namespace Pvra\tests\PhpParser\AnalysingNodeWalkers;

use Pvra\PhpParser\AnalysingNodeWalkers\LibraryAdditionsNodeWalker;
use Pvra\RequirementAnalysis\Result\RequirementReason;
use Pvra\tests\BaseNodeWalkerTestCase;

class LibraryAdditionsNodeWalkerTest extends BaseNodeWalkerTestCase
{
    protected $classToTest = 'Pvra\\PhpParser\\AnalysingNodeWalkers\\LibraryAdditionsNodeWalker';
    protected $expandNames = true;

    public function testMixedDetection()
    {
        $res = $this->runInstanceFromScratch('libraryAdditions');

        $expected = [
            [3, RequirementReason::FUNCTION_PRESENCE_CHANGE],
            [4, RequirementReason::FUNCTION_PRESENCE_CHANGE],
            [6, RequirementReason::FUNCTION_PRESENCE_CHANGE],
            [7, RequirementReason::CLASS_PRESENCE_CHANGE],
            [8, RequirementReason::CLASS_PRESENCE_CHANGE],
            [12, RequirementReason::CLASS_PRESENCE_CHANGE],
            [12, RequirementReason::CLASS_PRESENCE_CHANGE],
            [12, RequirementReason::CLASS_PRESENCE_CHANGE],
            [19, RequirementReason::CLASS_PRESENCE_CHANGE],
            [21, RequirementReason::CLASS_PRESENCE_CHANGE],
            [25, RequirementReason::CLASS_PRESENCE_CHANGE],
            [25, RequirementReason::CLASS_PRESENCE_CHANGE],
        ];

        $this->assertCount(count($expected) + /* 5.6 below the foreach */
            1, $res);

        foreach ($expected as $pos => $req) {
            $this->assertSame($req[0], $res->getRequirementInfo('5.4.0')[ $pos ]['line']);
            $this->assertSame($req[1], $res->getRequirementInfo('5.4.0')[ $pos ]['reason']);
        }

        $this->assertSame(4, $res->getRequirementInfo('5.6.0')[0]['line']);
        $this->assertSame(RequirementReason::FUNCTION_PRESENCE_CHANGE, $res->getRequirementInfo('5.6.0')[0]['reason']);
    }

    public function testPropertyOfNonObjectOnCountNamePartsInParameterTypeHint()
    {
        // this triggered a notice before the fix in this commit
        $result = $this->runInstanceFromScratch('libAdditionsPropOnNonObjInParamHint');
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testExpectedInvalidFileFormatException()
    {
        $walker = new LibraryAdditionsNodeWalker(null, TEST_FILE_ROOT . '/invalidNonExistingLibrarySource.php');
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Valid library data must have a "classes-added" list.
     */
    public function testExpectedClassKeyMissingException()
    {
        $walker = new LibraryAdditionsNodeWalker(null, ['functions-added' => []]);
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Valid library data must have a "classes-added" list.
     */
    public function testExpectedClassKeyListMissingException()
    {
        $walker = new LibraryAdditionsNodeWalker(null, ['functions-added' => [], 'classes-added' => '']);
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Valid library data must have a "functions-added" list.
     */
    public function testExpectedFunctionKeyMissingException()
    {
        $walker = new LibraryAdditionsNodeWalker(null, ['classes-added' => []]);
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Valid library data must have a "functions-added" list.
     */
    public function testExpectedFunctionKeyListMissingException()
    {
        $walker = new LibraryAdditionsNodeWalker(null, ['classes-added' => [], 'functions-added' => '']);
    }

    /**
     * @expectedExceptionMessage No valid, non-empty library information has been loaded. This should have happened in
     *     the constructor.
     * @expectedException \LogicException
     */
    public function testExceptionOnEmptyData()
    {
        $walker = new LibraryAdditionsNodeWalker(null, []);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The $libraryData parameter has to be a string or an array. SplFileInfo given.
     */
    public function testExpectedExceptionOnObjectParameterTypeOnConstruct()
    {
        $walker = new LibraryAdditionsNodeWalker(null,
            new \SplFileInfo(TEST_FILE_ROOT . '/invalidNonExistingLibrarySource.php'));
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The $libraryData parameter has to be a string or an array. resource given.
     */
    public function testExpectedExceptionOnWrongParameterTypeOnConstruct()
    {
        $walker = new LibraryAdditionsNodeWalker(null,
            fopen('php://memory', 'rw'));
    }
}
