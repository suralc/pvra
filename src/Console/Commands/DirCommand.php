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


use Pvra\AnalysisResult;
use Pvra\Result\Collection;
use Pvra\Result\Reasoning;
use RuntimeException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Finder\Finder;

/**
 * Class DirCommand
 *
 * @package Pvra\Console\Commands
 */
class DirCommand extends PvraBaseCommand
{
    const GROUP_BY_NAME = 'n',
        GROUP_BY_VERSION = 'v';

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this
            ->setName('analyse:dir')
            ->setDescription('Iterates over a directory and runs the requirement analysis');

        parent::configure();

        $this
            ->addOption('recursive', 'r', InputOption::VALUE_NONE, 'Iterate recursive over directory')
            ->addOption('groupBy', 'g', InputOption::VALUE_REQUIRED, 'Group output by name[n] or required version[v]',
                self::GROUP_BY_NAME)
            ->addArgument('filters', InputArgument::IS_ARRAY | InputArgument::OPTIONAL, 'Filter', ['name:*.php']);
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $dir = $input->getArgument('target');
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

        $this->applyIteratorFilter($files, $input->getArgument('filters'));

        $results = new Collection();

        /** @var \SplFileInfo $file */
        foreach ($files as $file) {
            if ($file->isFile()) {
                $req = $this->createFileAnalyserInstance($file->getPathname());
                $req->attachRequirementVisitors($this->createNodeWalkerInstances($input->getOption('libraryDataSource')));
                $results->add($req->run());
            }
        }

        if ($files->count() === 0) {
            $output->writeln('<error>No files processed!</error>');
            return;
        }

        if ($input->getOption('groupBy') === self::GROUP_BY_NAME) {
            $this->renderResultCollectionByName($results, $output, $input);
        } elseif ($input->getOption('groupBy') === self::GROUP_BY_VERSION) {
            $this->renderResultCollectionByRequiredVersion($results, $output);
        } else {
            throw new \InvalidArgumentException('The value given to the groupBy option is not allowed.');
        }

        if ($file = $input->getOption('saveAsFile')) {
            if (file_exists($file)) {
                $output->writeln(sprintf('<error>%s already exists. Cannot override an already existing file!</error>',
                    $file));
            } else {
                $output->writeln(sprintf('<info>Generating output file at %s</info>', $file));
                switch ($input->getOption('saveFormat')) {
                    case 'json': {
                        file_put_contents($file, json_encode($results));
                        break;
                    }
                    default: {
                        $output->writeln('<error>Invalid save format</error>');
                    }
                }
            }
        }
    }

    protected function renderResultCollectionByName(Collection $results, OutputInterface $out, InputInterface $in)
    {
        $highestRequirement = $results->getHighestDemandingResult();

        if ($highestRequirement === null) {
            throw new RuntimeException('Detection of requirements failed. Unknown error.');
        }
        $out->writeln('Highest required version: ' . $highestRequirement->getRequiredVersion());
        $out->writeln(sprintf('Required because %s uses following features:',
            $highestRequirement->getAnalysisTargetId()));

        foreach ($highestRequirement->getRequirements() as $version => $reasons) {
            foreach ($reasons as $reason) {
                $out->write("\t");
                $out->write($reason['msg'], true);
            }
        }

        if ($results->count() > 1) {
            $helper = $this->getHelper('question');
            $question = new ConfirmationQuestion('Continue with showing remaining ' . ($results->count() - 1) . ' results? [Y/n] ',
                'y');

            if (!$helper->ask($in, $out, $question)) {
                return;
            }
            $out->writeln('<info>Other results(' . ($results->count() - 1) . '):</info>');
            /** @var AnalysisResult $result */
            foreach ($results as $result) {
                if ($result->getAnalysisTargetId() === $highestRequirement->getAnalysisTargetId()) {
                    continue;
                }
                $out->write(implode('', [
                    'The file "',
                    $result->getAnalysisTargetId(),
                    '" requires PHP ',
                    $result->getRequiredVersion(),
                    ' for the following reasons:',
                    "\n"
                ]));
                /** @var  Reasoning */
                foreach ($result as $reason) {
                    $out->write("\t");
                    $out->write($reason['msg'], true);
                }
            }
        }
    }

    /**
     * @param \Pvra\Result\Collection $results
     * @param \Symfony\Component\Console\Output\OutputInterface $out
     */
    protected function renderResultCollectionByRequiredVersion(
        Collection $results,
        OutputInterface $out
        // InputInterface $in
    )
    {
        $highestRequirement = $results->getHighestDemandingResult();

        if ($highestRequirement === null) {
            throw new RuntimeException('Detection of requirements failed. Unknown error.');
        }
        $out->writeln('Highest required version: ' . $highestRequirement->getRequiredVersion() . ' in ' . $highestRequirement->getAnalysisTargetId() . ($results->count() > 1 ? ' and others' : ''));

        $usedVersions = [];
        /** @var AnalysisResult $result */
        foreach ($results as $result) {
            $versions = array_keys($result->getRequirements());
            foreach ($versions as $version) {
                $usedVersions[ $version ] = $version;
            }
        }

        usort($usedVersions, function ($a, $b) {
            return version_compare($b, $a);
        });

        foreach ($usedVersions as $version) {
            $out->writeln('Reasons for ' . $version);
            /** @var AnalysisResult $result */
            foreach ($results as $result) {
                $selectedResults = $result->getRequirementInfo($version);
                if (!empty($selectedResults)) {
                    foreach ($selectedResults as $reason) {
                        $out->write("\t");
                        $out->writeln($reason['msg']);
                    }
                }
            }
        }

    }

    /**
     * @param Finder $files
     * @param array $filterList
     */
    protected function applyIteratorFilter(Finder $files, array $filterList = [])
    {
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
