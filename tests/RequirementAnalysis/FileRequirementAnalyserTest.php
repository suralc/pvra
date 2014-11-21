<?php
namespace Pvra\tests\RequirementAnalysis;


use Pvra\RequirementAnalysis\FileRequirementAnalyser;
use Pvra\RequirementAnalysis\RequirementAnalysisResult;

class FileRequirementAnalyserTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \RunTimeException
     * @expectedExceptionMessage The file "nonExistingFile.php" could not be found or accessed.
     */
    public function testExceptionOnNonExistingFile()
    {
        $a = new FileRequirementAnalyser('nonExistingFile.php');
    }

    public function testAnalyisisTargetIdGeneration()
    {
        $a = new FileRequirementAnalyser(__FILE__);
        $result = new RequirementAnalysisResult();
        $a->setResultInstance($result);
        $this->assertSame(realpath(__FILE__), $result->getAnalysisTargetId());
    }

    public function testThatFileCanBeLoadedInParse()
    {
        // we get a warning if this fails. Need to think of a better way to test.
        $a = new FileRequirementAnalyser(__FILE__);
        $a->setResultInstance($result = new RequirementAnalysisResult());
        $a->run();
    }
}
