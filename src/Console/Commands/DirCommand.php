<?php

namespace Pvra\Console\Commands;


use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

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
            ->addArgument('filter', InputArgument::IS_ARRAY | InputArgument::OPTIONAL, 'Filter', ['*.php']);
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

        if ($input->getOption('recursive') == false) {
            $files->depth(0);
        }

        $this->applyIteratorFilterFromInput($input, $files);

        /** @var \SplFileInfo $file */
        foreach ($files as $file) {
            if ($file->isFile()) {
                $output->writeln('File: ' . $file->getRealPath());
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
