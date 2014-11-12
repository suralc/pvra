<?php

namespace Pvra\Console\Commands;


use PhpParser\Lexer\Emulative;
use PhpParser\Parser;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class DumpAstCommand extends Command
{
    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this
            ->setName('debug:dumpAst')
            ->setDescription('Dumps the AST of a file generated by PHP-Parser to the console');

        $this
            ->addOption('file', 'f', InputOption::VALUE_REQUIRED, 'File to dump')
            ->addOption('outputFormat', null, InputOption::VALUE_OPTIONAL, 'Output format (not implemented)', 'stdout');

    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $file = $input->getOption('file');

        if (!is_file($file) || !is_readable($file)) {
            $output->writeln(sprintf('<error>"%s" is not a valid file.</error>', $file));
            return;
        }

        $parser = new Parser(new Emulative);

        $stmts = $parser->parse(file_get_contents($file));

        print_r($stmts);
    }
}