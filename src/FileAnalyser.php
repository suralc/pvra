<?php
/**
 * FileAnalyser.php
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
namespace Pvra;

/**
 * Class FileAnalyser
 *
 * @package Pvra
 */
class FileAnalyser extends Analyser
{
    /**
     * The relative or absolute path to the file target of this analysis.
     *
     * @var string
     */
    private $filePath;

    /**
     * FileAnalyser constructor
     *
     * Validates the given file path and calls the parent's constructor.
     *
     * @param string $file The code to analyse
     * @param bool $registerNameResolver If set to true `PhpParser\NodeVisitor\NameResolver` will be added as the first
     *     visitor. This may negatively affect performance, some Visitors depend on resolved names, however.
     * @see \Pvra\RequirementAnalyser::__construct()
     */
    public function __construct($file, $registerNameResolver = true)
    {
        if (!$this->isFileValid($file)) {
            throw new \RuntimeException(sprintf('The file "%s" could not be found or accessed.', $file));
        }

        $this->filePath = realpath($file);
        parent::__construct($registerNameResolver);
    }

    /**
     * Validate a given file path.
     *
     * @param string $file Path to the file
     * @return bool Returns true if $file is a file and is readable. Returns fails otherwise.
     */
    private function isFileValid($file)
    {
        return is_file($file) && is_readable($file);
    }

    /**
     * @inheritdoc
     */
    protected function createAnalysisTargetId()
    {
        return $this->filePath;
    }

    /**
     * @inheritdoc
     */
    protected function parse()
    {
        return $this->getParser()->parse(file_get_contents($this->filePath));
    }
}
