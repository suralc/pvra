<?php
/**
 * Php55Features.php
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
 * @author     suralc <suralc.github@gmail.com>
 * @license    http://opensource.org/licenses/MIT  MIT
 */
namespace Pvra\Analysers;


use PhpParser\Node;
use Pvra\AnalyserAwareInterface;
use Pvra\Result\Reason;

/**
 * Class Php55Features
 *
 * Supports the detection of following features:
 * * Generator definitions using the `yield` - keyword
 * * `finally`
 * * Usage of list in foreach `foreach($array() as list($a, $b)) {}`
 * * Arbitrary expressions in the `empty` construct
 * * Array and string dereferencing
 * * Classname resolution using `Name::class`
 *
 * @package Pvra\Analysers
 */
class Php55Features extends LanguageFeatureAnalyser implements AnalyserAwareInterface
{

    /**
     * @inheritdoc
     */
    public function enterNode(Node $node)
    {
        if ($this->mode & self::MODE_ADDITION) {
            if ($node instanceof Node\Expr\Yield_) {
                $this->getResult()->addRequirement(
                    Reason::GENERATOR_DEFINITION,
                    $node->getLine()
                );
            } elseif ($node instanceof Node\Stmt\TryCatch && $node->finallyStmts !== null) {
                $this->getResult()->addRequirement(
                    Reason::TRY_CATCH_FINALLY,
                    $node->getLine()
                );
            } elseif ($node instanceof Node\Stmt\Foreach_ && $node->valueVar instanceof Node\Expr\List_) {
                $this->getResult()->addRequirement(
                    Reason::LIST_IN_FOREACH,
                    $node->getLine()
                );
            } elseif ($node instanceof Node\Expr\Empty_
                && !($node->expr instanceof Node\Expr\Variable
                    || $node->expr instanceof Node\Expr\PropertyFetch
                    || $node->expr instanceof Node\Expr\StaticPropertyFetch
                    || $node->expr instanceof Node\Expr\ArrayDimFetch)
            ) {
                $this->getResult()->addRequirement(
                    Reason::EXPR_IN_EMPTY,
                    $node->getLine()
                );
            } elseif ($node instanceof Node\Expr\ArrayDimFetch
                && ($node->var instanceof Node\Expr\Array_
                    || $node->var instanceof Node\Scalar\String_
                )
            ) {
                $this->getResult()->addRequirement(
                    Reason::ARRAY_OR_STRING_DEREFERENCING,
                    $node->getLine()
                );
            } elseif ($node instanceof Node\Expr\ClassConstFetch && strcasecmp($node->name, 'class') === 0) {
                $this->getResult()->addRequirement(
                    Reason::CLASS_NAME_RESOLUTION,
                    $node->getLine()
                );
            }
        }
    }
}
