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
 * @author     suralc <thesurwaveing@gmail.com>
 * @license    http://opensource.org/licenses/MIT  MIT
 */
namespace Pvra\Console\Commands;


use Pvra\PhpParser\AnalysingNodeWalkers\LibraryAdditionsNodeWalker;
use Pvra\PhpParser\AnalysingNodeWalkers\Php54LanguageFeatureNodeWalker;
use Pvra\PhpParser\AnalysingNodeWalkers\Php55LanguageFeatureNodeWalker;
use Pvra\PhpParser\AnalysingNodeWalkers\Php56LanguageFeatureNodeWalker;
use Pvra\RequirementAnalysis\FileRequirementAnalyser;
use Pvra\RequirementAnalysis\RequirementAnalysisResult;
use Pvra\RequirementAnalysis\Result\ResultMessageFormatter;
use Pvra\RequirementAnalysis\Result\ResultMessageLocator;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
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
            ->setDescription('File Checker');

        parent::configure();

        $this->addOption('file', 'f', InputOption::VALUE_REQUIRED, 'The file to check');
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($input->getOption('preventNameExpansions')) {
            $output->writeln('<warn>Detection of newly introduced functions and classes may not work in namespaced contexts if you prevent name expansions</warn>');
        }

        $req = new FileRequirementAnalyser($input->getOption('file'),
            $input->getOption('preventNameExpansions') !== true);

        $req->attachRequirementVisitor(new Php54LanguageFeatureNodeWalker);
        $req->attachRequirementVisitor(new Php55LanguageFeatureNodeWalker);
        $req->attachRequirementVisitor(new Php56LanguageFeatureNodeWalker);
        $req->attachRequirementVisitor(new LibraryAdditionsNodeWalker);

        $result = (new RequirementAnalysisResult())
            ->setMsgFormatter(new ResultMessageFormatter(
                ResultMessageLocator::fromPhpFile(__DIR__ . '/../../../data/default_messages.php')
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
    }
}
