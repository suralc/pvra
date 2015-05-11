<?php
namespace Pvra\tests\Console\Commands;


use Pvra\Console\Commands\DirCommand;
use Pvra\Result\Reason;

class DirCommandTest extends PvraBaseCommandTestBase
{
    protected $commandToTest = 'analyse:dir';

    public function testEarlyExitOnZeroFilesFound()
    {
        $out = trim($this->execute([
            'target' => TEST_FILE_ROOT,
            'filters' => ['name:*.nonexistant'],
        ])->getDisplay(true));

        $this->assertTrue(stripos($out, 'No files processed!') !== false);
    }

    public function testListFilesOnlyFormat()
    {
        $format = <<<'FORMAT'
7 files to process.
%sExtendedFinder.php
%sNonImplementingNodeWalker.php
%slibAdditionsPropOnNonObjInParamHint.php
%slibraryAdditions.php
%smessageArray.php
%sno_requirement.php
%ssimple_lib_data_source.php
FORMAT;
        $out = trim($this->execute([
            'target' => TEST_FILE_ROOT,
            'filters' => ['name:*.php'],
            '--listFilesOnly' => true,
        ])->getDisplay(true));

        $this->assertStringMatchesFormat($format, $out);
    }

    public function testDirGroupByNameWithInteractiveY()
    {
        /** @var DirCommand $command */
        list($commandTester, $command) = $this->getBareInstances();
        $helper = $command->getHelper('question');
        $helper->setInputStream($this->getInputStream('yes\\n'));
        $commandTester->execute([
            'target' => TEST_FILE_ROOT . '/5.5',
            '--groupBy' => DirCommand::GROUP_BY_NAME,
        ]);
        $out = trim($commandTester->getDisplay(true));

        $this->assertStringMatchesFormatFile(COMMAND_FORMAT_FILE_ROOT . 'dir_55_by_name.txt', $out);
    }

    public function testDirGroupByNameWithInteractiveN()
    {
        /** @var DirCommand $command */
        list($commandTester, $command) = $this->getBareInstances();
        $helper = $command->getHelper('question');
        $helper->setInputStream($this->getInputStream('no\\n'));
        $commandTester->execute([
            'target' => TEST_FILE_ROOT . '/5.5',
            '--groupBy' => DirCommand::GROUP_BY_NAME,
        ]);
        $out = trim($commandTester->getDisplay(true));

        $this->assertStringMatchesFormatFile(COMMAND_FORMAT_FILE_ROOT . 'dir_55_by_name_n.txt', $out);
    }

    public function testDirGroupByVersion()
    {
        $out = trim($this->execute([
            'target' => TEST_FILE_ROOT . '/5.5',
            '--groupBy' => DirCommand::GROUP_BY_VERSION,
        ])->getDisplay(true));

        $this->assertStringMatchesFormatFile(COMMAND_FORMAT_FILE_ROOT . 'dir_55_by_version.txt', $out);
    }

    public function testNoRequirementsFoundInRenderByName()
    {
        $out = trim($this->execute([
            'target' => TEST_FILE_ROOT,
            '--groupBy' => DirCommand::GROUP_BY_NAME,
            'filters' => ['name:no_requirement.php']
        ])->getDisplay(true));

        $this->assertTrue(stripos($out, 'No requirements beyond the default version') !== false);
    }

    public function testFileExportJson()
    {
        $outFile = TEST_FILE_ROOT . '../testTmp/out.json';
        $out = trim($this->execute([
            'target' => TEST_FILE_ROOT,
            'filters' => ['name:NonImplementingNodeWalker.php'],
            '--saveAsFile' => $outFile,
            '--saveFormat' => 'json',
        ])->getDisplay(true));

        $this->assertFileExists($outFile);
        $content = json_decode(file_get_contents($outFile), true);
        $this->assertCount(1, $content);
        $firstFileContent = current($content);
        $this->assertNotEmpty($firstFileContent);
        $this->assertSame(Reason::NAMESPACE_DECLERATION, $firstFileContent[0]['reason']);
        foreach (['data', 'reason', 'reasonName', 'line', 'msg', 'raw_msg', 'version', 'targetId'] as $key) {
            $this->assertTrue(isset($firstFileContent[0][ $key ]));
        }
    }

    public function testErrorOnExistingFile()
    {
        $outFile = TEST_FILE_ROOT . '../testTmp/out.json';
        touch($outFile);
        $this->assertFileExists($outFile, 'This test depends on the existence of this file');
        $out = trim($this->execute([
            'target' => TEST_FILE_ROOT,
            'filters' => ['name:NonImplementingNodeWalker.php'],
            '--saveAsFile' => $outFile,
            '--saveFormat' => 'json',
        ])->getDisplay(true));

        $this->assertTrue(stripos($out, 'Cannot override an already existing file') !== false);
    }

    public function testErrorOnNonSupportedFileFormat()
    {
        $outFile = TEST_FILE_ROOT . '../testTmp/out.json';
        $out = trim($this->execute([
            'target' => TEST_FILE_ROOT,
            'filters' => ['name:NonImplementingNodeWalker.php'],
            '--saveAsFile' => $outFile,
            '--saveFormat' => 'non-existant',
        ])->getDisplay(true));


        $this->assertTrue(stripos($out, 'non-existant is not a supported save format') !== false);
        $this->assertFileNotExists($out);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The value given to the groupBy option is not supported.
     */
    public function testInvalidGroupByError()
    {
        $this->execute([
            'target' => TEST_FILE_ROOT,
            '--groupBy' => 'invalid',
        ]);
    }

    protected function getInputStream($input)
    {
        $stream = fopen('php://memory', 'r+', false);
        fputs($stream, $input);
        rewind($stream);

        return $stream;
    }
}
