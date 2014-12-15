<?php
/**
 * Php55LanguageFeatureNodeWalker.php
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
 * Class Php55LanguageFeatureNodeWalker
 *
 * Supports the detection of following features:
 * * Generator definitions using the `yield` - keyword
 * * `finally`
 * * Usage of list in foreach `foreach($array() as list($a, $b)) {}`
 * * Arbitrary expressions in the `empty` construct
 * * Array and string dereferencing
 * * Classname resolution using `Name::class`
 *
 * @package Pvra\PhpParser\AnalysingNodeWalkers
 */
class Php55LanguageFeatureNodeWalker extends LanguageFeatureAnalyser implements RequirementAnalyserAwareInterface
{

    /**
     * @inheritdoc
     */
    public function enterNode(Node $node)
    {
        if ($node instanceof Node\Expr\Yield_) {
            $this->getResult()->addRequirement(
                RequirementReason::GENERATOR_DEFINITION,
                $node->getLine(),
                'Usage of generators require php 5.5'
            );
        } elseif ($node instanceof Node\Stmt\TryCatch && $node->finallyStmts !== null) {
            $this->getResult()->addRequirement(
                RequirementReason::TRY_CATCH_FINALLY,
                $node->getLine(),
                'Usage of the finally keyword requires php 5.5'
            );
        } elseif ($node instanceof Node\Stmt\Foreach_ && $node->valueVar instanceof Node\Expr\List_) {
            $this->getResult()->addRequirement(
                RequirementReason::LIST_IN_FOREACH,
                $node->getLine(),
                'Usage of list in foreach ValueVar statement requires php 5.5'
            );
        } elseif ($node instanceof Node\Expr\Empty_ && !($node->expr instanceof Node\Expr\Variable)) {
            $this->getResult()->addRequirement(
                RequirementReason::EXPR_IN_EMPTY,
                $node->getLine(),
                'Usage of arbitrary expressions in empty statement requires php 5.5'
            );
        } elseif ($node instanceof Node\Expr\ArrayDimFetch
            && ($node->var instanceof Node\Expr\Array_
                || $node->var instanceof Node\Scalar\String
            )
        ) {
            $this->getResult()->addRequirement(
                RequirementReason::ARRAY_OR_STRING_DEREFERENCING,
                $node->getLine(),
                'Array and string literal dereferencing requires php 5.5'
            );
        } elseif ($node instanceof Node\Expr\ClassConstFetch && strcasecmp($node->name, 'class') === 0) {
            $this->getResult()->addRequirement(
                RequirementReason::CLASS_NAME_RESOLUTION,
                $node->getLine(),
                'Class name resolution via ::class requires php 5.5'
            );
        }
    }
}