<?php

namespace Pvra\Console\Commands;


use Pvra\PhpParser\AnalysingNodeWalkers\LibraryAdditionsNodeWalker;
use Pvra\PhpParser\AnalysingNodeWalkers\Php54LanguageFeatureNodeWalker;
use Pvra\PhpParser\AnalysingNodeWalkers\Php55LanguageFeatureNodeWalker;
use Pvra\PhpParser\AnalysingNodeWalkers\Php56LanguageFeatureNodeWalker;
use Pvra\RequirementAnalysis\FileRequirementAnalyser;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class FileCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('analyse:file')
            ->setDescription('File Checker');

        $this->addOption('file', 'f', InputOption::VALUE_REQUIRED, 'The file to check');
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $req = new FileRequirementAnalyser($input->getOption('file'));

        $req->attachRequirementVisitor(new Php54LanguageFeatureNodeWalker);
        $req->attachRequirementVisitor(new Php55LanguageFeatureNodeWalker);
        $req->attachRequirementVisitor(new Php56LanguageFeatureNodeWalker);
        $req->attachRequirementVisitor(new LibraryAdditionsNodeWalker);

        $result = $req->run();

        $output->writeln(sprintf('<info>Required version: %s</info>', $result->getRequiredVersion()));

        foreach (array_reverse($result->getRequirements()) as $version => $reasons) {
            $output->writeln('Version ' . $version);
            foreach ($reasons as $reason) {
                $output->write("\t");
                $output->write('Reason: ');
                $output->write($reason['msg']);
                $output->write(sprintf(' in %s.', $result->getAnalysisTargetId() . ':' . $reason['line']),
                    true);
            }
        }
    }
}
