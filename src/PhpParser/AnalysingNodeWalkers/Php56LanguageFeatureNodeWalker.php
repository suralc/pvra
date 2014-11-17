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
                        $this->getResult()->addRequirement(
                            RequirementReason::VARIADIC_ARGUMENT,
                            $param->getLine(),
                            'Variadic arguments require php 5.6'
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
                        $this->getResult()->addRequirement(
                            RequirementReason::ARGUMENT_UNPACKING,
                            $arg->getLine(),
                            'Argument unpacking requires php 5.6'
                        );
                    }
                }
            }

        } elseif ($node instanceof Node\Stmt\Const_ || $node instanceof Node\Stmt\ClassConst) {
            foreach ($node->consts as $const) {
                if (!($const->value instanceof Node\Scalar)) {
                    $this->getResult()->addRequirement(
                        RequirementReason::CONSTANT_SCALAR_EXPRESSION,
                        $const->getLine(),
                        'Constant scalar expressions require php 5.6'
                    );
                }
            }
        } elseif ($node instanceof Node\Expr\AssignOp\Pow || $node instanceof Node\Expr\BinaryOp\Pow) {
            $this->getResult()->addRequirement(
                RequirementReason::POW_OPERATOR,
                $node->getLine(),
                'The "pow" operator requires php 5.6'
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
                $this->getResult()->addRequirement($cat, $node->getLine(), $msg);
            }
        }
    }
}
