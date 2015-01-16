<?php
namespace Pvra\tests;


use Pvra\AnalysisResult;
use Pvra\FileAnalyser;

class FileAnalyserTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \RunTimeException
     * @expectedExceptionMessage The file "nonExistingFile.php" could not be found or accessed.
     */
    public function testExceptionOnNonExistingFile()
    {
        $a = new FileAnalyser('nonExistingFile.php');
    }

    /**
     * @expectedException \RunTimeException
     */
    public function testExceptionOnNonFile()
    {
        $a = new FileAnalyser(__DIR__);
    }

    public function testAnalysisTargetIdGeneration()
    {
        $a = new FileAnalyser(__FILE__);
        $result = new AnalysisResult();
        $a->setResultInstance($result);
        $this->assertSame(realpath(__FILE__), $result->getAnalysisTargetId());
    }
}
