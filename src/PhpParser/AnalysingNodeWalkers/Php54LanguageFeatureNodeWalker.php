<?php
/**
 * Php54LanguageFeatureNodeWalker.php
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
 * Class Php54LanguageFeatureNodeWalker
 *
 * This class can be used to detect php 5.4 features.
 * Following features are supported:
 * * Trait definition using the `trait` keyword
 * * Trait using syntax
 * * Magic trait constant: `__TRAIT__`
 * * Array function dereferencing
 * * Callable typhint: `callable`
 * * Detection of `$this` in closures
 * 
 * @package Pvra\PhpParser\AnalysingNodeWalkers
 */
class Php54LanguageFeatureNodeWalker extends LanguageFeatureAnalyser implements RequirementAnalyserAwareInterface
{
    /**
     * Closure nesting level
     *
     * @var int
     */
    private $inClosureLevel = 0;

    /**
     * @inheritdoc
     */
    public function enterNode(Node $node)
    {
        if ($node instanceof Node\Stmt\Trait_) {
            $this->getResult()->addRequirement(
                RequirementReason::TRAIT_DEFINITION,
                $node->getLine()
            );
        } elseif ($node instanceof Node\Stmt\TraitUse) {
            $this->getResult()->addRequirement(
                RequirementReason::TRAIT_USE,
                $node->getLine()
            );
        } elseif ($node instanceof Node\Scalar\MagicConst\Trait_) {
            $this->getResult()->addRequirement(
                RequirementReason::TRAIT_MAGIC_CONST,
                $node->getLine()
            );
        } elseif ($node instanceof Node\Expr\ArrayDimFetch) {
            if ($node->var instanceof Node\Expr\FuncCall
                || $node->var instanceof Node\Expr\MethodCall
                || $node->var instanceof Node\Expr\StaticCall
            ) {
                $this->getResult()->addRequirement(
                    RequirementReason::ARRAY_FUNCTION_DEREFERENCING,
                    $node->getLine()
                );
            } elseif ($this->inClosureLevel > 0
                && $node->var instanceof Node\Expr\Variable && $node->var->name === 'this'
            ) {
                $this->_addThisInClosure($node->var);
            }
        } elseif ($node instanceof Node\Stmt\Function_
            || $node instanceof Node\Stmt\ClassMethod
            || $node instanceof Node\Expr\Closure
        ) {
            if (!empty($node->params)) {
                foreach ($node->params as $param) {
                    if ((string)$param->type === 'callable') {
                        $this->getResult()->addRequirement(
                            RequirementReason::TYPEHINT_CALLABLE,
                            $param->getLine()
                        );
                    }
                }
            }
        } elseif ($node instanceof Node\Expr\MethodCall) {
            if ($node->var instanceof Node\Expr\New_) {
                $this->getResult()->addRequirement(
                    RequirementReason::INSTANT_CLASS_MEMBER_ACCESS,
                    $node->getLine()
                );
            } elseif ($this->inClosureLevel > 0
                && $node->var instanceof Node\Expr\Variable && $node->var->name === 'this'
            ) {
                $this->_addThisInClosure($node->var);
            }
        } elseif ($this->inClosureLevel > 0 && $node instanceof Node\Expr\PropertyFetch
            && $node->var instanceof Node\Expr\Variable && $node->var->name === 'this'
        ) {
            $this->_addThisInClosure($node->var);
        } elseif ($this->inClosureLevel > 0 && $node instanceof Node\Expr\FuncCall
            && $node->name instanceof Node\Expr\Variable && $node->name->name === 'this'
        ) {
            $this->_addThisInClosure($node->name);
        } elseif ($node instanceof Node\Scalar\LNumber) {
            if ($node->hasAttribute('originalValue')) {
                if (stripos($node->getAttribute('originalValue'), '0b') !== false) {
                    $this->getResult()->addRequirement(RequirementReason::BINARY_NUMBER_DECLARATION, $node->getLine());
                }
            } else {
                throw new \LogicException("Missing attribute");
            }
        } elseif ($node instanceof Node\Expr\Array_) {
            if (!$node->hasAttribute('traditionalArray')) {
                $this->getResult()->addRequirement(RequirementReason::SHORT_ARRAY_DECLARATION, $node->getLine());
            }
        }

        if ($node instanceof Node\Expr\Closure) {
            $this->inClosureLevel++;
        }
    }

    /**
     * Leave node
     *
     * Currently only used to decrease closure level.
     *
     * @param \PhpParser\Node $node
     * @return void
     * @see Php54LanguageFeatureNodeWalker::$inClosureLevel Closure Level
     */
    public function leaveNode(Node $node)
    {
        if ($node instanceof Node\Expr\Closure) {
            $this->inClosureLevel--;
        }
    }

    /**
     * Wrapper for message addition in closure-this context
     *
     * @param \PhpParser\Node $node
     */
    private function _addThisInClosure(Node $node)
    {
        $this->getResult()->addRequirement(RequirementReason::THIS_IN_CLOSURE, $node->getLine());
    }
}
