<?php

namespace Pvra\PhpParser\AnalysingNodeWalkers;


use PhpParser\Node;
use Pvra\PhpParser\RequirementAnalyserAwareInterface;
use Pvra\RequirementAnalysis\Result\RequirementCategory;

class Php55LanguageFeatureNodeWalker extends LanguageFeatureAnalyser implements RequirementAnalyserAwareInterface
{
    /**
     * @inheritdoc
     */
    public function enterNode(Node $node)
    {
        if ($node instanceof Node\Expr\Yield_) {
            $this->getOwningAnalyser()->getResult()->addRequirement(
                '5.5.0',
                ['file' => $this->getOwningAnalyser()->getResult()->getAnalysisTargetId(), 'line' => $node->getLine()],
                'Usage of generators require php 5.5',
                RequirementCategory::GENERATOR_DEFINITION
            );
        }
    }
}
