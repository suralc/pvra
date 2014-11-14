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
                [
                    'file' => $this->getOwningAnalyser()->getResult()->getAnalysisTargetId(),
                    'line' => $node->getLine()
                ],
                'Usage of generators require php 5.5',
                RequirementCategory::GENERATOR_DEFINITION
            );
        } elseif ($node instanceof Node\Stmt\TryCatch && $node->finallyStmts !== null) {
            $this->getOwningAnalyser()->getResult()->addRequirement(
                '5.5.0',
                [
                    'file' => $this->getOwningAnalyser()->getResult()->getAnalysisTargetId(),
                    'line' => $node->getLine()
                ],
                'Usage of the finally keyword requires php 5.5',
                RequirementCategory::TRY_CATCH_FINALLY);

        } elseif ($node instanceof Node\Stmt\Foreach_ && $node->valueVar instanceof Node\Expr\List_) {
            $this->getOwningAnalyser()->getResult()->addRequirement(
                '5.5.0',
                [
                    'file' => $this->getOwningAnalyser()->getResult()->getAnalysisTargetId(),
                    'line' => $node->getLine(),
                ],
                'Usage of list in foreach ValueVar statement requires php 5.5',
                RequirementCategory::LIST_IN_FOREACH
            );
        } elseif ($node instanceof Node\Expr\Empty_ && !($node->expr instanceof Node\Expr\Variable)) {
            $this->getOwningAnalyser()->getResult()->addRequirement(
                '5.5.0',
                [
                    'file' => $this->getOwningAnalyser()->getResult()->getAnalysisTargetId(),
                    'line' => $node->getLine(),
                ],
                'Usage of arbitrary expressions in empty statement requires php 5.5',
                RequirementCategory::EXPR_IN_EMPTY
            );
        } elseif ($node instanceof Node\Expr\ArrayDimFetch
            && ($node->var instanceof Node\Expr\Array_
                || $node->var instanceof Node\Scalar\String
            )
        ) {
            $this->getOwningAnalyser()->getResult()->addRequirement(
                '5.5.0',
                [
                    'file' => $this->getOwningAnalyser()->getResult()->getAnalysisTargetId(),
                    'line' => $node->getLine(),
                ],
                'Array and string literal dereferencing requires php 5.5',
                RequirementCategory::ARRAY_STRING_DEREFERENCING
            );
        } elseif ($node instanceof Node\Expr\ClassConstFetch && strcasecmp($node->name, 'class') === 0) {
            $this->getOwningAnalyser()->getResult()->addRequirement(
                '5.5.0',
                [
                    'file' => $this->getOwningAnalyser()->getResult()->getAnalysisTargetId(),
                    'line' => $node->getLine(),
                ],
                'Class name resolution via ::class requires php 5.5',
                RequirementCategory::CLASS_NAME_RESOLUTION
            );
        }
    }
}
