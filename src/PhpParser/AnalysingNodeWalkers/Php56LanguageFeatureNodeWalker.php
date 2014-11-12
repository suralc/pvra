<?php
namespace Pvra\PhpParser\AnalysingNodeWalkers;


use PhpParser\Node;
use Pvra\PhpParser\RequirementAnalyserAwareInterface;
use Pvra\RequirementAnalysis\Result\RequirementCategory;

class Php56LanguageFeatureNodeWalker extends LanguageFeatureAnalyser implements RequirementAnalyserAwareInterface
{

    /**
     * @inheritdoc
     */
    public function enterNode(Node $node)
    {
        if ($node instanceof Node\Stmt\Function_
            || $node instanceof Node\Stmt\ClassMethod
            || $node instanceof Node\Expr\Closure
        ) {
            if (!empty($node->params)) {
                foreach ($node->params as $param) {
                    if ($param->variadic) {
                        $this->getOwningAnalyser()->getResult()->addRequirement(
                            '5.6.0',
                            [
                                'file' => $this->getOwningAnalyser()->getResult()->getAnalysisTargetId(),
                                'line' => $param->getLine()
                            ],
                            'Variadic arguments require php 5.6',
                            RequirementCategory::FUNCTION_VARIADIC
                        );
                    }
                }
            }
        }
    }

}
