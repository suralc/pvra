<?php
/**
 * SelfUpdateCommand.php
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
 * @author     suralc <suralc.github@gmail.com>
 * @license    http://opensource.org/licenses/MIT  MIT
 */
namespace Pvra\Console\Commands;


use Phar;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

/**
 * Class SelfUpdateCommand
 *
 * @package Pvra\Console\Commands
 */
class SelfUpdateCommand extends Command
{
    const DEFAULT_API_ROOT = 'https://api.github.com/';
    const DEFAULT_REPO_SLUG = 'suralc/pvra';

    private $apiRoot = self::DEFAULT_API_ROOT;
    private $slug = self::DEFAULT_REPO_SLUG;

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this->setName('self-update')
            ->setAliases(['selfupdate', 'update'])
            ->setDescription('Checks for a newer tagged version in the github repository.');
        $this->addOption('repo-slug', 'r', InputOption::VALUE_REQUIRED, 'Source repository', self::DEFAULT_REPO_SLUG)
            ->addOption('api-root', 'a', InputOption::VALUE_REQUIRED, 'Api root url', self::DEFAULT_API_ROOT);
    }

    protected function initialize(InputInterface $in, OutputInterface $out)
    {
        $this->apiRoot = $in->getOption('api-root');
        $this->slug = $in->getOption('repo-slug');
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $in, OutputInterface $out)
    {
        if (Phar::running() === '') {
            $out->writeln('<error>This command can only be used to upgrade the phar. Use composer if you run pvra from source</error>');
            return 2;
        }
        $out->writeln('Checking version for updates...');
        $out->writeln('Attempting to connect to repository: ' . $this->slug);
        list($ghReleases, $header) = $this->getReleases();
        if ($in->getOption('verbose')) {
            $headers = $this->parseHeader($header);
            $out->writeln('Github api limit: ' . $headers['X-RateLimit-Limit']);
            $out->writeln('Github api limit remaining: ' . $headers['X-RateLimit-Remaining']);
        }
        $ghReleasesContent = json_decode($ghReleases, true);
        unset($ghReleases);
        if (!empty($ghReleasesContent)) {
            $remoteVersion = substr($ghReleasesContent[0]['tag_name'], 1);
            $out->writeln('Your version: ' . $this->getApplication()->getVersion()
                . '(' . $this->getComparableVersion() . ')');
            $out->writeln('Current remote version is: ' . $remoteVersion);
            $compared = version_compare($this->getComparableVersion(), $remoteVersion);
            if ($compared < 0) {
                $out->writeln('A newer version is available.');
                $question = new ConfirmationQuestion('Do you wish to upgrade to ' . $remoteVersion . ' ? [y/N]: ');
                if ($this->getHelper('question')->ask($in, $out, $question)) {
                    $out->writeln('Upgrading...');
                    $temp = tmpfile();
                    stream_copy_to_stream(fopen($this->getPharUrlFromAssetArray($ghReleasesContent[0]['assets']), 'rb'),
                        $temp);
                    rewind($temp);
                    $running = Phar::running(false);
                    if (!is_writable($running)) {
                        throw new \RuntimeException('The current process needs to be able to delete ' . $running);
                    }
                    unlink($running);
                    $target = fopen($running, 'w+');
                    stream_copy_to_stream($temp, $target);
                    fclose($target);
                    fclose($temp);
                    $out->writeln($running . ' has  been successfully updated.');
                }
                return 0;
            } elseif ($compared > 0) {
                $out->writeln('The local version is ahead of the remote version');
                return 0;
            }
            $out->writeln('Remote and local version are equal');
            return 0;
        }
        $out->writeln('No releases were found. The phar can\'t be updated automatically.');
        return 0;
    }

    private function getNewDefaultStreamContext()
    {
        $version = $this->getApplication()->getVersion();
        return stream_context_create([
            'http' => [
                'header' => "User-Agent: Php-Version-Requirement-Analyser(pvra) by @suralc V{$version}\r\n" .
                    "Accept: application/vnd.github.v3+json\r\n",
            ],
        ]);
    }

    private function getReleases($target = '')
    {
        $response = file_get_contents(sprintf('%srepos/%s/releases%s', $this->apiRoot, $this->slug, $target), 0,
            $this->getNewDefaultStreamContext());
        return [$response, $http_response_header];
    }

    private function parseHeader(array $headerArray)
    {
        $headers = [];
        foreach ($headerArray as $num => $headerLine) {
            if ($num === 0) {
                // status code
                continue;
            }
            list($key, $value) = explode(':', $headerLine);
            $headers[ $key ] = $value;
        }

        return $headers;
    }

    private function getComparableVersion()
    {
        static $comparableVersion;
        if ($comparableVersion === null) {
            $localVersion = ltrim($this->getApplication()->getVersion(), 'v');
            preg_match('/(?P<major>[\\d]+).(?P<minor>[\\d]+).(?P<patch>[\\d]+)-(?P<commitCount>[\\d]+)-(g)?(?P<hash>[0-9a-f]{5,40})/i',
                $localVersion, $matches);
            $comparableVersion = sprintf('%d.%d.%d', $matches['major'], $matches['minor'], $matches['patch']);
            if (isset($matches['commitCount'])) {
                $comparableVersion .= '.' . $matches['commitCount'];
            }
        }
        return $comparableVersion;
    }

    /**
     * @param array $assets
     * @return string
     * @throws \Exception
     */
    private function getPharUrlFromAssetArray(array $assets)
    {
        if (!empty($assets)) {
            foreach ($assets as $asset) {
                if ($asset['name'] === 'pvra.phar') {
                    return $asset['browser_download_url'];
                }
            }
        }
        throw new \Exception('No valid asset found.');
    }
}
