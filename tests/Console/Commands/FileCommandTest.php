<?php

namespace Pvra\tests\Console\Commands;

require_once TEST_FILE_ROOT . 'NonImplementingNodeWalker.php';

use Symfony\Component\Console\Tester\CommandTester;

class FileCommandTest extends PvraBaseCommandTestBase
{
    protected $commandToTest = 'analyse:file';

    public function testSimpleExecuteWithSingleNodeWalker()
    {
        /** @var CommandTester $commandTester */
        list($commandTester) = $this->getBareInstances();
        $commandTester->execute([
            'target' => TEST_FILE_ROOT . '5.4/all54.php',
            '--analyser' => ['Php56Features'],
        ]);

        $out = trim($commandTester->getDisplay(true));
        $this->assertTrue(strpos($out, 'PHP 5.6.0') !== false);
        $this->assertTrue(strpos($out, ':5') !== false);
        $this->assertSame(1, preg_match_all('/(^\\s+[\\w]+: [\\w\\\\s$ ]+\\d\\.\\d+\\.\\d+ in .+:\\d+$)/m', $out));
    }

    public function testSimpleExecuteWithSingleMatchingNodeWalker()
    {
        $out = trim($this->execute([
            'target' => TEST_FILE_ROOT . '5.4/all54.php',
            '--analyser' => ['Php54Features'],
        ])->getDisplay(true));

        $this->assertTrue(strpos($out, 'PHP 5.6.0') === false);
        $this->assertSame(19, preg_match_all('/(^\\s+[\\w]+: [\\w\\\\s$ ]+\\d\\.\\d+\\.\\d+ in .+:\\d+$)/m', $out));
    }

    public function testAliasedAnalyserExecution()
    {
        $out = trim($this->execute([
            'target' => TEST_FILE_ROOT . '5.6/all56.php',
            '--analyser' => ['php-5.3'],
        ])->getDisplay(true));

        $this->assertTrue(stripos($out, 'PHP 5.6') === false);
        $this->assertTrue(stripos($out, 'PHP 5.5') === false);
        $this->assertTrue(stripos($out, 'PHP 5.4') === false);
        $this->assertSame(2, substr_count($out, 'PHP 5.3'));
    }

    public function testAliasedAnalysersExecution()
    {
        $out = trim($this->execute([
            'target' => TEST_FILE_ROOT . '5.6/all56.php',
            '--analyser' => ['php-5.3', 'lib-php', 'php-5.6'],
        ])->getDisplay(true));

        $this->assertTrue(stripos($out, 'PHP 5.6') !== false);
        $this->assertTrue(stripos($out, 'PHP 5.5') === false);
        $this->assertTrue(stripos($out, 'PHP 5.4') === false);
        $this->assertSame(2, substr_count($out, 'PHP 5.3'));
        $this->assertSame(16, substr_count($out, 'PHP 5.6'));
    }

    public function testExclusiveLibraryChangesWalker()
    {
        $cmdt = $this->execute([
            'target' => TEST_FILE_ROOT . 'libraryAdditions.php',
            '--analyser' => ['Php54Features', 'LibraryChanges']
        ]);
        $out = trim($cmdt->getDisplay(true));

        $this->assertTrue(substr_count($out, '5.6.0') === 3);
        $this->assertTrue(strpos($out, 'session_status') !== false);
        $this->assertSame(15,
            preg_match_all("/(^[.\\s]+.+\\\"(?P<req_name>[a-zA-Z\\_]+)\\\" (?P<type>function|class).+\\d\\.\\d+\\.\\d+ in .+\\:(?P<line_num>\\d+)$)/m",
                $out));
    }

    public function testWithCustomMessageSourceForNodeWalkers()
    {
        $out = trim($this->execute([
            'target' => TEST_FILE_ROOT . '5.4/all54.php',
            '--analyser' => ['Php54Features'],
            '--messageFormatSourceFile' => TEST_FILE_ROOT . 'msg_file_cmd.json',
        ])->getDisplay(true));

        $this->assertTrue(substr_count($out, 'Custom msg. Requires') === 2);
        $this->assertTrue(substr_count($out, 'Message for reason') === 18);
    }

    public function testNameExpansionWarning()
    {
        $out = trim($this->execute([
            'target' => TEST_FILE_ROOT . '5.4/all54.php',
            '--preventNameExpansion' => true
        ])->getDisplay(true));

        $this->assertTrue(stripos($out, 'Warning: Detection') !== false);
    }

    public function testNoWarningOnMissingExpansionWithoutLibraryWalker()
    {
        $out = trim($this->execute([
            'target' => TEST_FILE_ROOT . '5.4/all54.php',
            '--analyser' => ['Php54Features'],
            '--preventNameExpansion' => true
        ])->getDisplay(true));

        $this->assertTrue(strpos($out, 'Warning: Detection') === false);
    }

    public function testJsonResultExport()
    {
        $outFile = TEST_FILE_ROOT . '../testTmp/out.json';
        $this->assertFileNotExists($outFile);
        $out = trim($this->execute([
            'target' => TEST_FILE_ROOT . '5.4/all54.php',
            '--saveAsFile' => $outFile,
            '--saveFormat' => 'json'
        ])->getDisplay(true));

        $this->assertTrue(strpos($out, 'Generating output file') !== false);
        $this->assertFileExists($outFile);
        $outContent = json_decode(file_get_contents($outFile), true);
        foreach ($outContent as $reason) {
            foreach (['data', 'reason', 'reasonName', 'line', 'msg', 'raw_msg', 'version', 'targetId'] as $key) {
                $this->assertArrayHasKey($key, $reason);
            }
        }
    }

    public function testInvalidSaveFormatMessage()
    {
        $outFile = TEST_FILE_ROOT . '../testTmp/out.json';
        $out = trim($this->execute([
            'target' => TEST_FILE_ROOT . '5.4/all54.php',
            '--saveAsFile' => $outFile,
            '--saveFormat' => 'arcanist'
        ])->getDisplay(true));

        $this->assertTrue(strpos($out, 'Invalid save format') !== false);
    }

    public function testMessageOnAlreadyExistingSaveFile()
    {
        $outFile = TEST_FILE_ROOT . '../testTmp/out.json';
        touch($outFile);
        $out = trim($this->execute([
            'target' => TEST_FILE_ROOT . '5.4/all54.php',
            '--saveAsFile' => $outFile,
            '--saveFormat' => 'json'
        ])->getDisplay(true));

        $this->assertTrue(strpos($out, 'already exists. Cannot override an already existing file!') !== false);
        $this->assertTrue(strpos($out, 'Generating output file') === false);
    }

    public function testImportFromCustomLibraryDataFile()
    {
        $out = trim($this->execute([
            'target' => TEST_FILE_ROOT . 'libraryAdditions.php',
            '--analyser' => ['LibraryChanges'],
            '--libraryDataSource' => TEST_FILE_ROOT . 'simple_lib_data_source.php'
        ])->getDisplay(true));

        $this->assertTrue(substr_count($out, 'function') === 1);
        $this->assertTrue(strpos($out, 'substr') !== false);
        $this->assertTrue(strpos($out, '5.6.3') !== false);
    }

    public function testErrorMessageIsDisplayedOnInvalidFile()
    {
        try {
            $this->execute([
                'target' => TEST_FILE_ROOT . 'non-existant.php',
            ])->getDisplay(true);
        } catch (\InvalidArgumentException $ex) {
            $this->assertStringMatchesFormat('The target argument with value "%s" is not a valid file.',
                $ex->getMessage());
            return;
        } catch (\Exception $ex) {
            $this->fail('Unexpected exception with message: ' . $ex->getMessage());
        }
        $this->fail('Expected exception was not caught.');
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The values given to the "analyser" parameter are not valid.
     */
    public function testErrorOnInvalidAnalyserConfiguration()
    {
        $this->execute([
            'target' => TEST_FILE_ROOT . '5.4/all54.php',
            '--analyser' => 'Php54Features',
        ])->getDisplay(true);
    }

    public function testErrorOnNonExistingNodeWalkerClass()
    {
        try {
            $this->execute([
                'target' => TEST_FILE_ROOT . '5.4/all54.php',
                '--analyser' => ['NonExistingClass'],
            ])->getDisplay(true);
        } catch (\InvalidArgumentException $e) {
            $this->assertStringMatchesFormat('"%s" is not a class.', $e->getMessage());
            return;
        } catch (\Exception $e) {
            $this->fail('Unexpected exception with message: ' . $e->getMessage());
        }
        $this->fail('Expected exception was not caught.');
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage "Pvra\Analysers\NonImplementingNodeWalker" does not implement "Pvra\AnalyserAwareInterface"
     */
    public function testErrorOnNonImplementingNodeWalker()
    {
        $this->execute([
            'target' => TEST_FILE_ROOT . '5.4/all54.php',
            '--analyser' => ['Pvra\Analysers\NonImplementingNodeWalker'],
        ]);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage is not readable or does not exist.
     */
    public function testErrorOnMissingMessageFormatSourceFile()
    {
        $this->execute([
            'target' => TEST_FILE_ROOT . '5.4/all54.php',
            '--messageFormatSourceFile' => TEST_FILE_ROOT . 'non-existing.php',
        ]);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Only php and json files are supported for this operation
     */
    public function testErrorOnNonSupportedMessageFormatSourceFileFormat()
    {
        $this->execute([
            'target' => TEST_FILE_ROOT . '5.4/all54.php',
            '--messageFormatSourceFile' => TEST_FILE_ROOT . 'messageSource.xml',
        ]);
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage failed with notice:
     */
    public function testErrorOnInvalidJsonFile()
    {
        $this->execute([
            'target' => TEST_FILE_ROOT . '5.4/all54.php',
            '--messageFormatSourceFile' => TEST_FILE_ROOT . 'invalid_formed_json.json',
        ]);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage "php-over-9000" is not a class.
     */
    public function testErrorOnInvalidAnalyserAlias()
    {
        $this->execute([
            'target' => TEST_FILE_ROOT . '5.4/all54.php',
            '--analyser' => ['php-over-9000'],
        ]);
    }
}
