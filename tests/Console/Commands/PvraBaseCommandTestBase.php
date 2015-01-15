<?php

namespace Pvra\tests\Console\Commands;


use Pvra\Console\Application;
use Pvra\Console\Commands\DirCommand;
use Pvra\Console\Commands\FileCommand;
use Symfony\Component\Console\Tester\CommandTester;

abstract class PvraBaseCommandTestBase extends \PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    protected $commandToTest;

    public function tearDown()
    {
        if (file_exists(TEST_FILE_ROOT . '../testTmp/out.php')) {
            unlink(TEST_FILE_ROOT . '../testTmp/out.php');
        }
        if (file_exists(TEST_FILE_ROOT . '../testTmp/out.json')) {
            unlink(TEST_FILE_ROOT . '../testTmp/out.json');
        }
    }

    protected function getBareInstances()
    {
        $application = new Application();
        $application->add(new DirCommand());
        $application->add(new FileCommand());

        $command = $application->find($this->commandToTest);
        $commandTester = new CommandTester($command);

        return [$commandTester, $command, $application];
    }

    /**
     * @param array $argumetns
     * @param array $options
     * @param null $command
     * @param null $application
     * @return \Symfony\Component\Console\Tester\CommandTester
     */
    protected function execute($argumetns = [], $options = [], &$command = null, &$application = null)
    {
        /** @var CommandTester $commandTester */
        /** @var FileCommand $command */
        list($commandTester, $command, $application) = $this->getBareInstances();
        $commandTester->execute($argumetns, $options);
        return $commandTester;
    }
}
