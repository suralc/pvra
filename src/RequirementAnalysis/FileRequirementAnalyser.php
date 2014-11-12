<?php

namespace Pvra\RequirementAnalysis;


class FileRequirementAnalyser extends RequirementAnalyser
{
    /**
     * @var string
     */
    private $filePath;

    /**
     * @param string $file The code to analyse
     * @param bool $registerNameResolver
     */
    public function __construct($file, $registerNameResolver = true)
    {
        $this->filePath = realpath($file);
        parent::__construct($registerNameResolver);
    }

    /**
     * @return string
     */
    protected function createAnalysisTargetId()
    {
        if ($this->isFileValid()) {
            return realpath($this->filePath);
        } else {
            return 'invalid file descriptor'; // todo handle this better, merge with parse or something
        }
    }

    /**
     * @return bool
     */
    private function isFileValid()
    {
        return is_file($this->filePath) && is_readable($this->filePath);
    }


    /**
     * @return \PhpParser\Node[]
     */
    protected function parse()
    {
        if (!$this->isFileValid()) {
            throw new \RuntimeException(sprintf('The file "%s" could not be found or accessed', $this->filePath));
        }

        return $this->getParser()->parse(file_get_contents($this->filePath));
    }
}
