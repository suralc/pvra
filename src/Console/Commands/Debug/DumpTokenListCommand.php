<?php
/**
 * DumpTokenListCommand.php
 *
 * MIT LICENSE
 *
 * LICENSE: This source file is subject to the MIT license.
 * A copy of the licenses text was distributed alongside this
 * file (usually the repository or package root). The text can also
 * be obtained on one of the following sources:
 * * http://opensource.org/licenses/MIT
 * * https://github.com/suralc/pvra/blob/master/LICENSE
 *
 * @author     suralc <thesurwaveing@gmail.com>
 * @license    http://opensource.org/licenses/MIT  MIT
 */

namespace Pvra\Console\Commands\Debug;


use PhpParser\Lexer;
use Pvra\Lexer\ExtendedEmulativeLexer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class DumpTokenListCommand
 *
 * @package Pvra\Console\Commands\Debug
 */
class DumpTokenListCommand extends Command
{
    protected function configure()
    {
        $this->setName('debug:dumpTokens')
            ->setDescription('Dump list of tokens in given file');

        $this->addArgument('target', InputArgument::REQUIRED, 'The target file');
        $this->addOption('tokenizer', 't', InputOption::VALUE_REQUIRED, 'The used tokenizer. Either php or lib', 'php');
    }

    protected function execute(InputInterface $in, OutputInterface $out)
    {
        $file = $in->getArgument('target');
        if (!is_file($file) || !is_readable($file)) {
            $out->writeln(sprintf('<error>"%s" is not a valid file!</error>', $file));
            exit;
        }
        $tokenizer = $in->getOption('tokenizer');
        if ($tokenizer === 'php') {
            $tokens = token_get_all(file_get_contents($file));

            foreach ($tokens as $token) {
                if (is_array($token)) {
                    $out->writeln(sprintf('%s(%s) "%s"', token_name($token[0]), $token[0], $token[1]));
                } else {
                    $out->writeln('"' . $token . '"');
                }
            }
        } elseif ($tokenizer === 'lib') {
            $lexer = ExtendedEmulativeLexer::createDefaultInstance();
            $lexer->startLexing(file_get_contents($file));
            $offset = 0;
            while ($id = $lexer->getNextToken()) {
                if (is_string($name = $this->libTokenToTokenName($id))) {
                    $out->writeln($name . '(' . $lexer->getTokens()[ $offset ][0] . ') ' . $lexer->getTokens()[ $offset ][1]);
                } else {
                    $out->writeln('Discarded token with id ' . $id);
                }
                $offset++;
            }
        } else {
            $out->writeln(sprintf('<error>"%s" is not a valid value for the tokenizer option</error>', $tokenizer));
        }
    }

    /**
     * @param int $value
     * @return string|null
     */
    private function libTokenToTokenName($value)
    {
        static $resolved;
        if ($resolved === null) {
            $refl = new \ReflectionClass('\PhpParser\Parser');
            $consts = $refl->getConstants();
            $resolved = array_flip($consts);
        }

        return isset($resolved[ $value ]) ? $resolved[ $value ] : null;
    }
}
