<?php

namespace Pvra\Console\Commands;


use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class CheckUpdateCommand
 * @package Pvra\Console\Commands
 */
class CheckUpdateCommand extends Command
{
    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this->setName('update:check')
            ->setDescription('Checks if there is a newer tagged version in the github repository');
        $this->addArgument('repo-name', InputArgument::OPTIONAL, 'Source repository', 'suralc/pvra');
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $in, OutputInterface $out)
    {
        $version = $this->getApplication()->getVersion();
        $repoName = $in->getArgument('repo-name');
        $out->writeln('Checking version for updates...');
        $out->writeln('Current version is: ' . $version);
        $out->writeln('Attemping to connect: ' . $repoName);
        $ghReleases = file_get_contents("https://api.github.com/repos/{$repoName}/releases", 0,
            stream_context_create([
                'http' => [
                    'header' => "User-Agent: Php-Version-Requirement-Checker V{$version}\r\n" .
                        "Accept: application/vnd.github.v3+json\r\n",
                ]
            ])
        );
    }
}
