<?php
/**
 * DirCommand.php
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


use Pvra\PhpParser\AnalysingNodeWalkers\Php54LanguageFeatureNodeWalker;
use Pvra\PhpParser\AnalysingNodeWalkers\Php55LanguageFeatureNodeWalker;
use Pvra\PhpParser\AnalysingNodeWalkers\Php56LanguageFeatureNodeWalker;
use Pvra\RequirementAnalysis\FileRequirementAnalyser;
use Pvra\RequirementAnalysis\Result\ResultCollection;
use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

/**
 * Class DirCommand
 *
 * @package Pvra\Console\Commands
 */
class DirCommand extends Command
{
    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this
            ->setName('analyse:dir')
            ->setDescription('Iterates over a directory and runs the requirement analysis');

        $this
            ->addOption('dir', 'd', InputOption::VALUE_OPTIONAL, 'The directory to check', '.')
            ->addOption('recursive', 'r', InputOption::VALUE_NONE, 'Iterate recursive over directory')
            ->addArgument('filter', InputArgument::IS_ARRAY | InputArgument::OPTIONAL, 'Filter', ['name:*.php']);
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $dir = $input->getOption('dir');
        if ($output->getVerbosity() >= OutputInterface::VERBOSITY_VERY_VERBOSE) {
            $output->writeln('Dir: ' . $dir);
        }

        if (!is_dir($dir) || !is_readable($dir)) {
            throw new RuntimeException(sprintf('"%s" is not a valid directory', $dir));
        }

        $files = (new Finder())
            ->files()
            ->in($dir);

        if ($input->getOption('recursive') === false) {
            $files->depth(0);
        }

        $this->applyIteratorFilterFromInput($input, $files);


        $results = new ResultCollection();

        /** @var \SplFileInfo $file */
        foreach ($files as $file) {
            if ($file->isFile()) {
                $req = new FileRequirementAnalyser($file->getPathname());

                $req->attachRequirementVisitor(new Php54LanguageFeatureNodeWalker);
                $req->attachRequirementVisitor(new Php55LanguageFeatureNodeWalker);
                $req->attachRequirementVisitor(new Php56LanguageFeatureNodeWalker);

                $results->add($req->run());
            }
        }

        $higestRequirement = $results->getHighestDemandingResult();

        if ($higestRequirement === null) {
            // todo better handling
            $output->writeln('Unknown error');
            return;
        }

        $output->writeln('Required version: ' . $higestRequirement->getRequiredVersion());
        $output->writeln(sprintf('Required because %s uses following featrues:',
            $higestRequirement->getAnalysisTargetId()));

        foreach ($higestRequirement->getRequirements() as $version => $reasons) {
            foreach ($reasons as $reason) {
                $output->write("\t");
                $output->write(sprintf('%s required on line %s: %s', $version, $reason['location']['line'],
                    $reason['msg']), true);
            }
        }

    }

    /**
     * @param InputInterface $input
     * @param Finder $files
     */
    protected function applyIteratorFilterFromInput(InputInterface $input, Finder $files)
    {
        $filterList = $input->getArgument('filter');

        if (!empty($filterList) && is_array($filterList)) {
            foreach ($filterList as $currentFilter) {
                if (!stripos($currentFilter, ':')) {
                    throw new \InvalidArgumentException(sprintf('The filter "%s" is not a valid filter',
                        $currentFilter));
                }

                $currentFilterElements = explode(':', $currentFilter);

                if (count($currentFilterElements) !== 2) {
                    throw new \InvalidArgumentException(sprintf('The filter "%s" is not a valid filter',
                        $currentFilter));
                }

                switch ($currentFilterElements[0]) {
                    case 'exclude':
                        $files->exclude($currentFilterElements[1]);
                        break;
                    case 'name':
                        $files->name($currentFilterElements[1]);
                        break;
                    case 'notName':
                        $files->notName($currentFilterElements[1]);
                        break;
                    case 'path':
                        $files->path($currentFilterElements[1]);
                        break;
                    case 'size':
                        $files->size($currentFilterElements[1]);
                }
            }
        }
    }
}
