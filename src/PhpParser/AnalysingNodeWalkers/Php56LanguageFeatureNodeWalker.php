<?php
namespace Pvra\PhpParser\AnalysingNodeWalkers;


use PhpParser\Node;
use Pvra\PhpParser\RequirementAnalyserAwareInterface;
use Pvra\RequirementAnalysis\Result\RequirementReason;

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
                            RequirementReason::VARIADIC_ARGUMENT
                        );
                    }
                }
            }
        } elseif ($node instanceof Node\Expr\FuncCall
            || $node instanceof Node\Expr\MethodCall
            || $node instanceof Node\Expr\StaticCall
        ) {
            if (!empty($node->args)) {
                foreach ($node->args as $arg) {
                    if ($arg->unpack === true) {
                        $this->getOwningAnalyser()->getResult()->addRequirement(
                            '5.6.0',
                            [
                                'file' => $this->getOwningAnalyser()->getResult()->getAnalysisTargetId(),
                                'line' => $arg->getLine()
                            ],
                            'Argument unpacking requires php 5.6',
                            RequirementReason::ARGUMENT_UNPACKING
                        );
                    }
                }
            }

        } elseif ($node instanceof Node\Stmt\Const_) {
            foreach ($node->consts as $const) {
                if (!($const->value instanceof Node\Scalar)) {
                    $this->getOwningAnalyser()->getResult()->addRequirement(
                        '5.6.0',
                        [
                            'file' => $this->getOwningAnalyser()->getResult()->getAnalysisTargetId(),
                            'line' => $const->getLine(),
                        ],
                        'Constant scalar expressions require php 5.6',
                        RequirementReason::CONSTANT_SCALAR_EXPRESSION
                    );
                }
            }
        } elseif ($node instanceof Node\Expr\AssignOp\Pow || $node instanceof Node\Expr\BinaryOp\Pow) {
            $this->getOwningAnalyser()->getResult()->addRequirement(
                '5.6.0',
                [
                    'file' => $this->getOwningAnalyser()->getResult()->getAnalysisTargetId(),
                    'line' => $node->getLine()
                ],
                'The "pow" operator requires php 5.6',
                RequirementReason::POW_OPERATOR
            );
        } elseif ($node instanceof Node\Stmt\Use_) {
            $msg = '';
            $cat = null;
            if ($node->type === Node\Stmt\Use_::TYPE_CONSTANT) {
                $msg = 'Constant import via use requires php 5.6';
                $cat = RequirementReason::CONSTANT_IMPORT_USE;
            } elseif ($node->type === Node\Stmt\Use_::TYPE_FUNCTION) {
                $msg = 'Function import via use requires php 5.6';
                $cat = RequirementReason::FUNCTION_IMPORT_USE;
            }

            if ($cat !== null) {
                $this->getOwningAnalyser()->getResult()->addRequirement(
                    '5.6.0',
                    [
                        'file' => $this->getOwningAnalyser()->getResult()->getAnalysisTargetId(),
                        'line' => $node->getLine(),
                    ],
                    $msg,
                    $cat
                );
            }
        }
    }
}
