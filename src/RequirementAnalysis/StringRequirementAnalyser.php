<?php

namespace Pvra\RequirementAnalysis;


class StringRequirementAnalyser extends RequirementAnalyser
{
    private $string;

    /**
     * @param string $string The code to analyse
     * @param bool $registerNameResolver
     */
    public function __construct($string, $registerNameResolver = true)
    {
        $this->string = $string;
        parent::__construct($registerNameResolver);
    }

    /**
     * @return \PhpParser\Node[]
     */
    protected function parse()
    {
        return $this->getParser()->parse($this->string);
    }

    /**
     * @return string
     */
    protected function createAnalysisTargetId()
    {
        return md5($this->string);
    }
}