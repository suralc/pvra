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

    /**
     * @expectedException \RunTimeException
     */
    public function testExceptionOnNonFile()
    {
        $a = new FileRequirementAnalyser(__DIR__);
    }

    public function testAnalysisTargetIdGeneration()
    {
        $a = new FileRequirementAnalyser(__FILE__);
        $result = new RequirementAnalysisResult();
        $a->setResultInstance($result);
        $this->assertSame(realpath(__FILE__), $result->getAnalysisTargetId());
    }
}
