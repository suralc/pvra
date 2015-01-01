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


use Pvra\RequirementAnalysis\FileRequirementAnalyser;
use Pvra\RequirementAnalysis\Result\ResultMessageLocator;
use Symfony\Component\Console\Command\Command;
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
    const WALKER_DEFAULT_NAMESPACE_ROOT = 'Pvra\\PhpParser\\AnalysingNodeWalkers\\';

    /**
     * A list of `Pvra\PhpParser\RequirementAnalyserAwareInterface` class names.
     *
     * @var array|string[]
     */
    protected $expectedWalkers = [];

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this
            ->addOption('extensive', 'x', InputOption::VALUE_NONE, 'Use more extensive output format (var_dump like)')
            ->addOption('preventNameExpansion', 'p', InputOption::VALUE_NONE,
                'Prevent name expansion. May increase performance but sacrifices some functionality')
            ->addOption('analyser', 'a', InputOption::VALUE_IS_ARRAY | InputOption::VALUE_REQUIRED,
                'Analysers to run', $this->getDefaultAnalysers())
            ->addOption('libraryDataSource', 'l', InputOption::VALUE_REQUIRED, 'Source file of library data', false)
            ->addOption('messageFormatSourceFile', 'm', InputOption::VALUE_REQUIRED, 'File with message formats', false)
            ->addOption('saveFormat', null, InputOption::VALUE_REQUIRED, 'The format of the save file.', 'json')
            ->addOption('saveAsFile', null, InputOption::VALUE_REQUIRED,
                'Save the output as file. Requires usage of the format option.', false);

        $this->addArgument('target', InputArgument::REQUIRED, 'The target of this analysis');
    }

    protected function getDefaultAnalysers()
    {
        return [
            'Php54LanguageFeatureNodeWalker',
            'Php55LanguageFeatureNodeWalker',
            'Php56LanguageFeatureNodeWalker',
            'LibraryAdditionsNodeWalker',
        ];
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $analysers = $input->getOption('analyser');
        if (empty($analysers) || !is_array($analysers)) {
            throw new \InvalidArgumentException('The values given to the "analyser" parameter are not valid.');
        }
        foreach ($analysers as $analyser) {
            $analyserName = self::WALKER_DEFAULT_NAMESPACE_ROOT . $analyser;
            if (!class_exists($analyserName)) {
                throw new \InvalidArgumentException(sprintf('"%s" (expanded to "%s") is not a class.', $analyser,
                    $analyserName));
            } elseif (!in_array('Pvra\\PhpParser\\RequirementAnalyserAwareInterface', class_implements($analyserName))
            ) {
                throw new \InvalidArgumentException(sprintf('"%s" does not implement "%s"', $analyserName,
                    'Pvra\\PhpParser\\RequirementAnalyserAwareInterface'));
            }
            $this->expectedWalkers[] = $analyserName;
        }
        $this->expectedWalkers = array_unique($this->expectedWalkers);
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface|string $input
     * @param null|bool $preventNameExpansion
     * @return \Pvra\RequirementAnalysis\FileRequirementAnalyser
     */
    protected function createFileAnalyserInstance($input, $preventNameExpansion = null)
    {
        return new FileRequirementAnalyser($input instanceof InputInterface ? $input->getArgument('target') : $input,
            $input instanceof InputInterface ? $input->getOption('preventNameExpansion') !== true : (bool)$preventNameExpansion);
    }

    protected function createMessageLocatorInstance(InputInterface $input)
    {
        $file = $input->getOption('messageFormatSourceFile');
        $locator = null;
        if (is_string($file)) {
            $locator = ResultMessageLocator::fromArray($this->getArrayFromFile($file)[1]);
        } else {
            $locator = ResultMessageLocator::fromPhpFile(__DIR__ . '/../../../data/default_messages.php');
        }

        $locator->addMissingMessageHandler(function () {
            return 'Message for reason :id: not found. Required version: :version:';
        });

        return $locator;
    }

    /**
     * @param string $librarySourceOption
     * @return \Pvra\PhpParser\AnalysingNodeWalkers\LanguageFeatureAnalyser[]
     */
    protected function createNodeWalkerInstances($librarySourceOption = null)
    {
        $analysers = [];
        foreach ($this->expectedWalkers as $walker) {
            if (stripos($walker, 'Library') !== false && is_string($librarySourceOption)) {
                $analysers[] = new $walker(null, $this->getArrayFromFile($librarySourceOption)[1]);
            } else {
                $analysers[] = new $walker;
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
                case 'php': {
                    return [$type, include $filePath];
                }
                case 'json': {
                    if (($data = json_decode(file_get_contents($filePath), true)) === null) {
                        throw new \RuntimeException(sprintf('Json decoding of file "%s" failed with notice: "%s"',
                            $filePath,
                            version_compare(PHP_VERSION, '5.5.0', '>=') ? json_last_error_msg() : json_last_error()));
                    }
                    return [$type, $data];
                }
                default: {
                    throw new \InvalidArgumentException(sprintf('The %s filetype is not supported. Only php and json files are supported for this operation.',
                        $type));
                }
            }
        }

        throw new \InvalidArgumentException(sprintf('The file "%s" is not readable or does not exist.', $filePath));
    }
}
