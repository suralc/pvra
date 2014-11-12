<?php
namespace Pvra\PhpParser;


use PhpParser\NodeVisitor;
use Pvra\RequirementAnalysis\RequirementAnalyser;

interface RequirementAnalyserAwareInterface extends NodeVisitor
{
    /**
     * @param RequirementAnalyser $requirementAnalyser
     * @return void
     */
    public function setOwningAnalyser(RequirementAnalyser $requirementAnalyser);

    /**
     * @return RequirementAnalyser
     */
    public function getOwningAnalyser();
}