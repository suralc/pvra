<?php
/**
 * Php54Features.php
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
 * Class Php54Features
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
 * @package Pvra\PhpParser\Analysers
 */
class Php54Features extends LanguageFeatureAnalyser implements AnalyserAwareInterface
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
        if ($node instanceof Node\Expr\Closure) {
            $this->inClosureLevel++;
        }

        if ($this->mode & self::MODE_ADDITION) {
            $this->handleTraitFeatures($node);
            $this->handleFunctionDereferencing($node);
            $this->handleCallableType($node);
            $this->handleInstantClassMemberAccess($node);
            $this->handleThisInClosure($node);
            $this->handleBinaryNumberDeclaration($node);
            $this->handleShortArrayDeclaration($node);
            $this->handleStaticCallByExpressionSyntax($node);
            $this->detectShortEchoSyntax($node);
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
    private function addThisInClosureRequirement(Node $node)
    {
        $this->getResult()->addRequirement(Reason::THIS_IN_CLOSURE, $node->getLine());
    }

    /**
     * @param \PhpParser\Node $node
     */
    private function handleTraitFeatures(Node $node)
    {
        if ($node instanceof Node\Stmt\Trait_) {
            $this->getResult()->addRequirement(
                Reason::TRAIT_DEFINITION,
                $node->getLine()
            );
        } elseif ($node instanceof Node\Stmt\TraitUse) {
            $this->getResult()->addRequirement(
                Reason::TRAIT_USE,
                $node->getLine()
            );
        } elseif ($node instanceof Node\Scalar\MagicConst\Trait_) {
            $this->getResult()->addRequirement(
                Reason::TRAIT_MAGIC_CONST,
                $node->getLine()
            );
        }
    }

    /**
     * @param \PhpParser\Node $node
     */
    private function handleFunctionDereferencing(Node $node)
    {
        if ($node instanceof Node\Expr\ArrayDimFetch && ($node->var instanceof Node\Expr\FuncCall
                || $node->var instanceof Node\Expr\MethodCall
                || $node->var instanceof Node\Expr\StaticCall)
        ) {
            $this->getResult()->addRequirement(
                Reason::ARRAY_FUNCTION_DEREFERENCING,
                $node->getLine()
            );
        }
    }

    /**
     * @param \PhpParser\Node $node
     */
    private function handleCallableType(Node $node)
    {
        if (($node instanceof Node\Stmt\Function_
                || $node instanceof Node\Stmt\ClassMethod
                || $node instanceof Node\Expr\Closure)
            && !empty($node->params)
        ) {
            foreach ($node->params as $param) {
                if ((string)$param->type === 'callable') {
                    $this->getResult()->addRequirement(
                        Reason::TYPEHINT_CALLABLE,
                        $param->getLine()
                    );
                }
            }

        }
    }

    /**
     * @param \PhpParser\Node $node
     */
    private function handleInstantClassMemberAccess(Node $node)
    {
        if ($node instanceof Node\Expr\MethodCall && $node->var instanceof Node\Expr\New_) {
            $this->getResult()->addRequirement(
                Reason::INSTANT_CLASS_MEMBER_ACCESS,
                $node->getLine()
            );
        }
    }

    /**
     * @param \PhpParser\Node $node
     */
    private function handleThisInClosure(Node $node)
    {
        if ($this->inClosureLevel > 0) {
            if (($node instanceof Node\Expr\PropertyFetch || $node instanceof Node\Expr\ArrayDimFetch || $node instanceof Node\Expr\MethodCall)
                && $node->var instanceof Node\Expr\Variable
                && $node->var->name === 'this'
            ) {
                $this->addThisInClosureRequirement($node->var);
            } elseif ($node instanceof Node\Expr\FuncCall
                && $node->name instanceof Node\Expr\Variable
                && $node->name->name === 'this'
            ) {
                $this->addThisInClosureRequirement($node->name);
            }
        }
    }

    /**
     * @param \PhpParser\Node $node
     */
    private function handleBinaryNumberDeclaration(Node $node)
    {
        if ($node instanceof Node\Scalar\LNumber) {
            if ($node->hasAttribute('originalValue')) {
                if (stripos($node->getAttribute('originalValue'), '0b') !== false) {
                    $this->getResult()->addRequirement(Reason::BINARY_NUMBER_DECLARATION, $node->getLine());
                }
            } else {
                throw new \InvalidArgumentException('Node attribute ("originalValue") missing. Only nodes generated by ExtendedEmulativeLexer '
                    . 'or one of its children are supported.');
            }
        }
    }

    /**
     * @param \PhpParser\Node $node
     */
    private function handleShortArrayDeclaration(Node $node)
    {
        if ($node instanceof Node\Expr\Array_ && $node->getAttribute('traditionalArray', false) !== true) {
            $this->getResult()->addRequirement(Reason::SHORT_ARRAY_DECLARATION, $node->getLine());
        }
    }

    private function handleStaticCallByExpressionSyntax(Node $node)
    {
        if ($node instanceof Node\Expr\StaticCall && $node->name instanceof Node\Expr) {
            $this->getResult()->addRequirement(Reason::STATIC_CALL_BY_EXPRESSION, $node->getLine());
        }
    }

    private function detectShortEchoSyntax(Node $node)
    {
        if ($node instanceof Node\Stmt\Echo_ && $node->hasAttribute('isShortEchoTag')) {
            $this->getResult()->addRequirement(Reason::SHORT_ECHO_TAG, $node->getLine());
        }
    }
}
