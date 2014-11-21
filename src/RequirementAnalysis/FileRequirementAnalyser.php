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
        if (!$this->isFileValid($file)) {
            throw new \RuntimeException(sprintf('The file "%s" could not be found or accessed.', $file));
        }

        $this->filePath = realpath($file);
        parent::__construct($registerNameResolver);
    }

    /**
     * @inheritdoc
     */
    protected function createAnalysisTargetId()
    {
        return $this->filePath;
    }

    /**
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
    protected function parse()
    {
        return $this->getParser()->parse(file_get_contents($this->filePath));
    }
}
