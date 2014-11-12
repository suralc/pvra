<?php
namespace Pvra\PhpParser\AnalysingNodeWalkers;


use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;
use Pvra\PhpParser\RequirementAnalyserAwareInterface;
use Pvra\RequirementAnalysis\RequirementAnalyser;

abstract class LanguageFeatureAnalyser extends NodeVisitorAbstract implements RequirementAnalyserAwareInterface
{
    /**
     * @var RequirementAnalyser
     */
    private $requirementAnalyser;

    /**
     * @param RequirementAnalyser $requirementAnalyser
     */
    public function setOwningAnalyser(RequirementAnalyser $requirementAnalyser)
    {
        $this->requirementAnalyser = $requirementAnalyser;
    }

    /**
     * @return RequirementAnalyser
     */
    public function getOwningAnalyser()
    {
        return $this->requirementAnalyser;
    }
}
