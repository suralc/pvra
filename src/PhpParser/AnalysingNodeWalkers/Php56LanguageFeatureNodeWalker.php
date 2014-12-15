<?php
/**
 * Php56LanguageFeatureNodeWalker.php
 *
 * MIT LICENSE
 *
 * LICENSE: This source file is subject to the MIT license.
 * A copy of the licenses text was distributed alongside this
 * file (usually the repository or package root). The text can also
 * be obtained through one of the following sources:
 * * http://opensource.org/licenses/MIT
 * * https://github.com/suralc/pvra/blob/master/LICENSE
 *
 * @author     suralc <thesurwaveing@gmail.com>
 * @license    http://opensource.org/licenses/MIT  MIT
 */
namespace Pvra\PhpParser\AnalysingNodeWalkers;


use PhpParser\Node;
use Pvra\PhpParser\RequirementAnalyserAwareInterface;
use Pvra\RequirementAnalysis\Result\RequirementReason;

/**
 * Class Php56LanguageFeatureNodeWalker
 *
 * Supported syntax detection:
 * * Variadic arguments
 * * Argument unpacking
 * * Constant scalar expressions
 * * The `**`(pow) operator
 * * Function and Constant importing via `use`
 *
 * @package Pvra\PhpParser\AnalysingNodeWalkers
 */
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
