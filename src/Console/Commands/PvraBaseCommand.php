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
 * @author     suralc <suralc.github@gmail.com>
 * @license    http://opensource.org/licenses/MIT  MIT
 */
namespace Pvra\Console\Commands;


use Pvra\Analysers\LanguageFeatureAnalyser;
use Pvra\FileAnalyser;
use Pvra\InformationProvider\LibraryInformation;
use Pvra\Result\Collection;
use Pvra\Result\CollectionWriter;
use Pvra\Result\Exceptions\ResultFileWriterException;
use Pvra\Result\MessageLocator;
use Pvra\Result\ResultFormatter\Json;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * PvraBaseCommand
 *
 * Base class to give some default options for commands that may run the parser.
 *
 * @package Pvra\Console\Commands
 */
class PvraBaseCommand extends Command
{
    const WALKER_DEFAULT_NAMESPACE_ROOT = 'Pvra\\Analysers\\';

    /**
     * A list of `Pvra\PhpParser\RequirementAnalyserAwareInterface` class names.
     *
     * @var array|string[]
     */
    protected $expectedWalkers = [];

    /**
     * @var bool
     */
    private $preferRelativePaths = false;

    /**
     * List of analysers that are loaded if the --analyser option is not set
     *
     * @var array
     */
    private static $defaultAnalysers = [
        'Php53Features',
        'Php54Features',
        'Php55Features',
        'Php56Features',
//        'Php70Features', // do not default-enable 7.0 yet as support is only partial
        'LibraryChanges',
    ];

    private static $analyserAliasMap = [
        'Php53Features' => 'php-5.3',
        'Php54Features' => 'php-5.4',
        'Php55Features' => 'php-5.5',
        'Php56Features' => 'php-5.6',
        'Php70Features' => 'php-7.0',
        'LibraryChanges' => 'lib-php',
    ];

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this
            ->addOption('preferRelativePaths', 's', InputOption::VALUE_NONE, 'Prefer relative paths in the output')
            ->addOption('preventNameExpansion', 'p', InputOption::VALUE_NONE,
                'Prevent name expansion. May increase performance but breaks name based detections in namespaces.')
            ->addOption('analyser', 'a', InputOption::VALUE_IS_ARRAY | InputOption::VALUE_REQUIRED,
                'Analysers to run', $this->getDefaultAnalysersAliases())
            ->addOption('libraryDataSource', 'l', InputOption::VALUE_REQUIRED, 'Source file of library data', false)
            ->addOption('messageFormatSourceFile', 'm', InputOption::VALUE_REQUIRED, 'File with message formats', false)
            ->addOption('saveFormat', null, InputOption::VALUE_REQUIRED, 'The format of the save file.', 'json')
            ->addOption('saveAsFile', null, InputOption::VALUE_REQUIRED,
                'Save the output as file. Requires usage of the format option. Value is the targets file path.', false);

        $this->addArgument('target', InputArgument::REQUIRED, 'The target of this analysis');
    }

    /**
     * @return array
     */
    private function getDefaultAnalysersAliases()
    {
        $aliasNameMap = [];
        foreach (self::$defaultAnalysers as $analyser) {
            if (isset(self::$analyserAliasMap[ $analyser ])) {
                $aliasNameMap[] = self::$analyserAliasMap[ $analyser ];
            }
        }

        return $aliasNameMap;
    }

    /**
     * @param $name
     * @return bool|string
     */
    private function resolveAnalyserName($name)
    {
        if (($resolved = array_search($name, self::$analyserAliasMap)) !== false) {
            return $resolved;
        }
        return in_array($name, array_keys(self::$analyserAliasMap)) ? $name : false;
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        if ($input->getOption('preferRelativePaths')) {
            $this->preferRelativePaths = true;
        }
        $style = new OutputFormatterStyle('red', 'yellow', ['bold', 'blink']);
        $output->getFormatter()->setStyle('warn', $style);
        $analysers = $input->getOption('analyser');
        if (empty($analysers) || !is_array($analysers)) {
            throw new \InvalidArgumentException('The values given to the "analyser" parameter are not valid.');
        }
        foreach ($analysers as $analyser) {
            if (($name = $this->resolveAnalyserName($analyser)) !== false) {
                $analyserName = self::WALKER_DEFAULT_NAMESPACE_ROOT . $name;
            } else {
                $analyserName = $analyser;
            }
            if (!class_exists($analyserName)) {
                throw new \InvalidArgumentException(sprintf('"%s" is not a class.', $analyser));
            } elseif (!in_array('Pvra\\AnalyserAwareInterface', class_implements($analyserName))
            ) {
                throw new \InvalidArgumentException(sprintf('"%s" does not implement "%s"', $analyserName,
                    'Pvra\\AnalyserAwareInterface'));
            }
            $this->expectedWalkers[] = $analyserName;
        }
        $this->expectedWalkers = array_unique($this->expectedWalkers);
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface|string $input
     * @param null|bool $preventNameExpansion
     * @return \Pvra\FileAnalyser
     */
    protected function createFileAnalyserInstance($input, $preventNameExpansion = null)
    {
        return new FileAnalyser($input instanceof InputInterface ? $input->getArgument('target') : $input,
            $input instanceof InputInterface ? $input->getOption('preventNameExpansion') !== true : (bool)$preventNameExpansion);
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @return \Pvra\Result\MessageLocator|static
     */
    protected function createMessageLocatorInstance(InputInterface $input)
    {
        $file = $input->getOption('messageFormatSourceFile');
        if (is_string($file)) {
            $locator = MessageLocator::fromArray($this->getArrayFromFile($file)[1]);
        } else {
            $locator = MessageLocator::fromPhpFile(__DIR__ . '/../../../data/default_messages.php');
        }

        $locator->addMissingMessageHandler(function () {
            return 'Message for reason :id: not found. Required version: :version:';
        });

        return $locator;
    }

    /**
     * @param string $librarySourceOption
     * @param int $mode
     * @return \Pvra\Analysers\LanguageFeatureAnalyser[]
     */
    protected function createNodeWalkerInstances(
        $librarySourceOption = null,
        $mode = LanguageFeatureAnalyser::MODE_ADDITION
    ) {
        $analysers = [];
        foreach ($this->expectedWalkers as $walker) {
            if (in_array('Pvra\InformationProvider\LibraryInformationAwareInterface', class_implements($walker))) {
                if (is_string($librarySourceOption)) {
                    $info = new LibraryInformation($this->getArrayFromFile($librarySourceOption)[1]);
                } else {
                    $info = LibraryInformation::createWithDefaults();
                }
                $analysers[] = (new $walker(['mode' => $mode]))->setLibraryInformation($info);
            } else {
                $analysers[] = new $walker(['mode' => $mode]);
            }
        }

        return $analysers;
    }

    /**
     * @param string $filePath
     * @return array An array having the type of the given file at index `0` and its data at index `1`.
     */
    protected function getArrayFromFile($filePath)
    {
        if (is_file($filePath) && is_readable($filePath)) {
            $type = pathinfo($filePath, PATHINFO_EXTENSION);
            switch ($type) {
                case 'php':
                    return [$type, include $filePath];
                case 'json':
                    if (($data = json_decode(file_get_contents($filePath), true)) === null) {
                        throw new \RuntimeException(sprintf('Json decoding of file "%s" failed with notice: "%s"',
                            $filePath,
                            version_compare(PHP_VERSION, '5.5.0', '>=') ? json_last_error_msg() : json_last_error()));
                    }
                    return [$type, $data];
                default:
                    throw new \InvalidArgumentException(sprintf('The %s filetype is not supported. Only php and json files are supported for this operation.',
                        $type));
            }
        }

        throw new \InvalidArgumentException(sprintf('The file "%s" is not readable or does not exist.', $filePath));
    }

    /**
     * @return bool
     */
    protected function hasNameDependentAnalyser()
    {
        foreach ($this->expectedWalkers as $walker) {
            if (in_array('Pvra\InformationProvider\LibraryInformationAwareInterface',
                class_implements($walker))) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string $target
     * @param string $format
     * @param \Pvra\Result\Collection $result
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     */
    protected function writeToFile($target, $format, Collection $result, OutputInterface $output)
    {
        $output->writeln('Preparing to write results to ' . $target);
        try {
            $fileWriter = new CollectionWriter($result);
            switch ($format) {
                case 'json':
                    $formatter = new Json();
                    break;
                default:
                    throw new ResultFileWriterException($format . ' is not a supported save format');
            }
            $fileWriter->write($target, $formatter);
            $output->writeln(sprintf('<info>Generated output file at %s</info>', $target));
        } catch (ResultFileWriterException $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
        }
    }

    /**
     * @param string $target
     * @return string
     */
    protected function formatOutputPath($target)
    {
        if (!$this->preferRelativePaths) {
            return $target;
        }
        return \Pvra\Console\makeRelativePath(getcwd(), $target);
    }
}
