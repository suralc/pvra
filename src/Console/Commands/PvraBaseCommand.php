<?php
/**
 * PvraBaseCommand.php
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
            ->addOption('preventNameExpansions', 'p' ,InputOption::VALUE_NONE, 'Prevent name expansion.');
    }

}
