<?php
/**
 * CheckUpdateCommand.php
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
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;

/**
 * Class CheckUpdateCommand
 *
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
        $repoName = $in->getArgument('repo-name');
        $out->writeln('Checking version for updates...');
        $out->writeln('Current version is: ' . $version = $this->getApplication()->getVersion());
        $out->writeln('Attemping to connect: ' . $repoName);
        $ghReleases = $this->performGETApiRequest("repos/{$repoName}/releases");
        $ghReleasesContent = json_decode($ghReleases, true);
        unset($ghReleases);
        if (empty($ghReleasesContent)) {
            $out->writeln('No releases were found. Attemping to compare commit-hashes.');
            $branches = $this->performGETApiRequest("repos/{$repoName}/branches");
            $branches = json_decode($branches, true);
            if (empty($branches)) {
                $out->writeln('<error>Could not get branch information. Please check for updates yourself</error>');
                die(2);
            }
            $branchList = [];
            array_walk($branches, function ($value) use (&$branchList) {
                $branchList[] = $value['name'];
            });
            $question = new ChoiceQuestion('Compare hash to which branch?', $branchList, $branchList[0]);
            $question->setErrorMessage('Branch %s is invalid.');
            $helper = $this->getHelper('question');
            $branch = $helper->ask($in, $out, $question);
            $compareResult = $this->performGETApiRequest("repos/{$repoName}/compare/{$version}...{$branch}");
            if ($compareResult === false) {
                $out->writeln('<error>An error occurred. Please try again later or check for updates yourself</error>');
                die(2);
            }
            $compareResult = json_decode($compareResult, true);
            if (isset($compareResult['status'])) {
                if ($compareResult['status'] === 'ahead') {
                    $out->writeln('<info>Your version of pvra is newer than the remote branch you selected</info>');
                } elseif ($compareResult['status'] === 'behind') {
                    $out->writeln('<info>Your version of pvra is older than the remote branch you selected</info>');
                } else {
                    $out->writeln('<info>Your version of pvra and the remote branch are equal');
                }
            } else {
                $out->writeln('<error>No status could be determined</error>');
            }
            exit;
        } else {
            $out->writeln('<info>Implementation missing</info>');
        }
    }

    private function getNewDefaultStreamContext()
    {
        $version = $this->getApplication()->getVersion();
        return stream_context_create([
            'http' => [
                'header' => "User-Agent: Php-Version-Requirement-Analyser(pvra) by @suralc V{$version}\r\n" .
                    "Accept: application/vnd.github.v3+json\r\n",
            ]
        ]);
    }

    private function performGETApiRequest($target = '')
    {
        return @file_get_contents("https://api.github.com/" . $target, 0, $this->getNewDefaultStreamContext());
    }
}
