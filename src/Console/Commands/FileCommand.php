<?php
/**
 * FileCommand.php
 *
 * MIT LICENSE
 *
 * LICENSE: This source file is subject to the MIT license.
 * A copy of the licenses text was distributed alongside this
 * file (usually the repository or package root). The text can also
 * be obtained through one of the following sources:
 * * http://opensource.org/licenses/MIT
 * * https://github.com/suralc/pvra/blob/master/LICENSE
 *
 * @author     suralc <suralc.github@gmail.com>
 * @license    http://opensource.org/licenses/MIT  MIT
 */
namespace Pvra\Console\Commands;


use Pvra\AnalysisResult;
use Pvra\Result\MessageFormatter;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class FileCommand
 *
 * @package Pvra\Console\Commands
 */
class FileCommand extends PvraBaseCommand
{
    protected function configure()
    {
        $this
            ->setName('analyse:file')
            ->setDescription('Analyse the requirements of a given file.');

        parent::configure();
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $file = $input->getArgument('target');
        if (!is_file($file) || !is_readable($file)) {
            throw new \InvalidArgumentException(sprintf('The target argument with value "%s" is not a valid file.',
                $file));
        }

        $output->writeln(sprintf('<info>Running analysis for "%s"</info>', realpath($file)));


        if ($input->getOption('preventNameExpansion') && $this->hasNameDependentAnalyser()) {
            $output->writeln('<warn>Warning: Detection of newly introduced functions and classes may not work or produce'
                . ' false positives in namespaced contexts if you prevent name expansions</warn>');
        }

        $req = $this->createFileAnalyserInstance($input);

        $req->attachRequirementVisitors($this->createNodeWalkerInstances($input->getOption('libraryDataSource')));

        $result = (new AnalysisResult())
            ->setMsgFormatter(new MessageFormatter(
                $this->createMessageLocatorInstance($input)
            ), true, true);

        $req->setResultInstance($result);

        $req->run();

        $output->writeln(sprintf('<info>Required version: %s</info>', $result->getRequiredVersion()));

        foreach (array_reverse($result->getRequirements()) as $version => $reasons) {
            $output->writeln('Version ' . $version);
            foreach ($reasons as $reason) {
                $output->write("\t");
                $output->write('Reason: ');
                $output->write($reason['msg'], true);
            }
        }

        if ($file = $input->getOption('saveAsFile')) {
            if (file_exists($file)) {
                $output->writeln(sprintf('<error>%s already exists. Cannot override an already existing file!</error>',
                    $file));
            } else {
                $output->writeln(sprintf('<info>Generating output file at %s</info>', $file));
                $outData = [];
                foreach ($result->getIterator() as $r) {
                    $outData[] = $r;
                }
                switch ($input->getOption('saveFormat')) {
                    case 'json': {
                        file_put_contents($file, json_encode($outData));
                        break;
                    }
                    default: {
                        $output->writeln('<error>Invalid save format</error>');
                    }
                }
            }
        }
    }
}
