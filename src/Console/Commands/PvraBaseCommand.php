<?php

namespace Pvra\Console\Commands;


use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;

/**
 * PvraBaseCommand
 *
 * Base class to give some default options for commands that may run the parser.
 *
 * @package Pvra\Console\Commands
 */
class PvraBaseCommand extends Command
{
    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this
            ->addOption('outputFormat', null, InputOption::VALUE_OPTIONAL, 'Output format (not implemented)', 'stdout')
            ->addOption('extensive', 'x', InputOption::VALUE_NONE, 'Use more extensive output format (var_dump)')
            ->addOption('preventNameExpansions', InputOption::VALUE_NONE, 'Prevent name expansion.');
    }

}
