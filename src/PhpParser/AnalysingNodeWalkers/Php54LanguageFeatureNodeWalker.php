<?php

namespace Pvra\PhpParser\AnalysingNodeWalkers;


use PhpParser\Node;
use Pvra\PhpParser\RequirementAnalyserAwareInterface;
use Pvra\RequirementAnalysis\Result\RequirementReason;

class Php54LanguageFeatureNodeWalker extends LanguageFeatureAnalyser implements RequirementAnalyserAwareInterface
{
    /**
     * @inheritdoc
     */
    public function enterNode(Node $node)
    {
        if ($node instanceof Node\Stmt\Trait_) {
            $this->getOwningAnalyser()->getResult()->addRequirement(
                '5.4.0',
                ['file' => $this->getOwningAnalyser()->getResult()->getAnalysisTargetId(), 'line' => $node->getLine()],
                'Usage of the trait keyword requires PHP 5.4',
                RequirementReason::TRAIT_DEFINITION
            );
        } elseif ($node instanceof Node\Stmt\TraitUse) {
            $this->getOwningAnalyser()->getResult()->addRequirement(
                '5.4.0',
                ['file' => $this->getOwningAnalyser()->getResult()->getAnalysisTargetId(), 'line' => $node->getLine()],
                'Usage of trait imports requires PHP 5.4',
                RequirementReason::TRAIT_USE
            );
        } elseif ($node instanceof Node\Scalar\MagicConst\Trait_) {
            $this->getOwningAnalyser()->getResult()->addRequirement(
                '5.4.0',
                [
                    'file' => $this->getOwningAnalyser()->getResult()->getAnalysisTargetId(),
                    'line' => $node->getLine(),
                ],
                'Usage of the Trait magic constant requires php 5.4',
                RequirementReason::TRAIT_MAGIC_CONST
            );
        } elseif ($node instanceof Node\Expr\ArrayDimFetch) {
            if ($node->var instanceof Node\Expr\FuncCall
                || $node->var instanceof Node\Expr\MethodCall
                || $node->var instanceof Node\Expr\StaticCall
            ) {
                $this->getOwningAnalyser()->getResult()->addRequirement(
                    '5.4.0',
                    [
                        'file' => $this->getOwningAnalyser()->getResult()->getAnalysisTargetId(),
                        'line' => $node->getLine()
                    ],
                    'Function dereferencing requires php 5.4',
                    RequirementReason::ARRAY_FUNCTION_DEREFERENCING
                );
            }
        }
        if ($node instanceof Node\Stmt\Function_
            || $node instanceof Node\Stmt\ClassMethod
            || $node instanceof Node\Expr\Closure
        ) {
            if (!empty($node->params)) {
                foreach ($node->params as $param) {
                    if ($param->type === 'callable') {
                        $this->getOwningAnalyser()->getResult()->addRequirement(
                            '5.4.0',
                            [
                                'file' => $this->getOwningAnalyser()->getResult()->getAnalysisTargetId(),
                                'line' => $param->getLine()
                            ],
                            'The callable typehint requires php 5.4',
                            RequirementReason::TYPEHINT_CALLABLE
                        );
                    }
                }
            }

        }
        if ($node instanceof Node\Expr\MethodCall) {
            if ($node->var instanceof Node\Expr\New_) {
                $this->getOwningAnalyser()->getResult()->addRequirement(
                    '5.4.0',
                    [
                        'file' => $this->getOwningAnalyser()->getResult()->getAnalysisTargetId(),
                        'line' => $node->getLine()
                    ],
                    'Instant class member access requires php 5.4',
                    RequirementReason::INSTANT_CLASS_MEMBER_ACCESS
                );
            }
        }
        if ($node instanceof Node\Expr\Closure) {
            if (!empty($node->stmts)) {
                foreach ($node->stmts as $stmt) {
                    if ($stmt->hasAttribute('var') && $stmt->getAttribute('var')->name === 'this') {
                        $this->getOwningAnalyser()->getResult()->addRequirement(
                            '5.4.0',
                            [
                                'file' => $this->getOwningAnalyser()->getResult()->getAnalysisTargetId(),
                                'line' => $node->getLine()
                            ],
                            'Usage of $this in closures requires php 5.4',
                            RequirementReason::THIS_IN_CLOSURE
                        );
                    }
                }
            }
        }
    }
}
