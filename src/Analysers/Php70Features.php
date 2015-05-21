<?php
/**
 * Php70Features.php
 *
 * MIT LICENSE
 *
 * LICENSE: This source file is subject to the MIT license.
 * A copy of the licenses text was distributed alongside this
 * file (usually the repository or package root). The text can also
 * be obtained on one of the following sources:
 * * http://opensource.org/licenses/MIT
 * * https://github.com/suralc/pvra/blob/master/LICENSE
 *
 * @author     suralc <suralc.github@gmail.com>
 * @license    http://opensource.org/licenses/MIT  MIT
 */

namespace Pvra\Analysers;


use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\NodeTraverser;
use Pvra\AnalyserAwareInterface;
use Pvra\Result\Reason;

/**
 * Class Php70Features
 *
 * @package Pvra\Analysers
 */
class Php70Features extends LanguageFeatureAnalyser implements AnalyserAwareInterface
{
    // move both to be const once 5.6+ is mandatory
    private static $reservedNames = ['string', 'int', 'float', 'bool', 'null', 'false', 'true'];
    private static $softReservedNames = ['object', 'resource', 'mixed', 'numeric'];

    public function enterNode(Node $node)
    {
        if ($node instanceof Node\Stmt\Use_) {
            $this->detectAndHandleReservedNamesInUse($node);
            return NodeTraverser::DONT_TRAVERSE_CHILDREN;
        } elseif ($node instanceof Node\Stmt\ClassLike) {
            if ($node instanceof Node\Stmt\Class_) {
                if ($node->isAnonymous()) {
                    $this->handleAnonymousClass($node);
                }
                $this->detectAndHandlePhp4Ctor($node);
            }
            $this->detectAndHandleReservedNames($node);
        } elseif ($this->isFunctionLike($node)) {
            $this->detectAndHandleReturnTypeDeclaration($node);
        } elseif ($node instanceof Expr\FuncCall) {
            $this->detectAndHandleClassAliasCallToReservedName($node);
        } elseif ($node instanceof Expr\YieldFrom) {
            $this->handleYieldFrom($node);
        }
        $this->detectAndHandleOperatorAdditions($node);
        return null;
    }

    private function detectAndHandleReservedNamesInUse(Node\Stmt\Use_ $node)
    {
        if ($node->type === Node\Stmt\Use_::TYPE_NORMAL) {
            foreach ($node->uses as $use) {
                if ($use->alias === null || $use->alias === $use->name->getLast()) {
                    $this->handleClassName($use->name->toString(), $use->name->getLine());
                } else {
                    $this->handleClassName($use->alias, $use->getLine());
                }
            }
        }
    }

    private function detectAndHandlePhp4Ctor(Node\Stmt\Class_ $cls)
    {
        if ($this->mode & self::MODE_DEPRECATION && !$cls->isAnonymous()) {
            $name = isset($cls->namespacedName) ? $cls->namespacedName->toString() : $cls->name;
            $possibleCtorInfo = null;
            /** @var Node\Stmt\ClassMethod $method */
            foreach ($cls->getMethods() as $method) {
                if (strcasecmp($method->name, '__construct') === 0) {
                    return; // This will always be treated as ctor. Drop everything else
                } elseif (strcasecmp($method->name, ltrim($name, '\\')) === 0) {
                    $possibleCtorInfo = [
                        Reason::PHP4_CONSTRUCTOR,
                        $method->getLine(),
                        null,
                        ['name' => $method->name]
                    ];
                }
            }
            if ($possibleCtorInfo !== null) {
                call_user_func_array([$this->getResult(), 'addLimit'], $possibleCtorInfo);
            }
        }
    }

    private function detectAndHandleReservedNames(Node\Stmt\ClassLike $cls)
    {
        $this->handleClassName($cls->name, $cls->getLine());
    }

    private function isFunctionLike(Node $node)
    {
        return $node instanceof Node\Stmt\ClassMethod
        || $node instanceof Node\Stmt\Function_
        || $node instanceof Expr\Closure;
    }

    private function detectAndHandleReturnTypeDeclaration(Node $node)
    {
        if ($this->mode & self::MODE_ADDITION && $node->returnType !== null) {
            $this->getResult()->addRequirement(Reason::RETURN_TYPE, $node->getLine());
        }
    }

    private function detectAndHandleClassAliasCallToReservedName(Expr\FuncCall $call)
    {
        if ($call->name instanceof Node\Name && strcasecmp('class_alias', $call->name->getLast()) === 0
        ) {
            if (isset($call->args[1]) && $call->args[1]->value instanceof Node\Scalar\String_) {
                $value = $call->args[1]->value->value;
                $this->handleClassName($value, $call->args[1]->value->getLine());
            }
        }
    }

    private function detectAndHandleOperatorAdditions(Node $node)
    {
        if ($this->mode & self::MODE_ADDITION) {
            if ($node instanceof Expr\BinaryOp\Coalesce) {
                $this->getResult()->addRequirement(Reason::COALESCE_OPERATOR, $node->getLine());
            }
            if ($node instanceof Expr\BinaryOp\Spaceship) {
                $this->getResult()->addRequirement(Reason::SPACESHIP_OPERATOR, $node->getLine());
            }
        }
    }

    private function handleYieldFrom(Expr\YieldFrom $node)
    {
        if ($this->mode & self::MODE_ADDITION) {
            $this->getResult()->addRequirement(Reason::YIELD_FROM, $node->getLine());
        }
    }

    private function handleAnonymousClass(Node $node)
    {
        if ($this->mode & self::MODE_ADDITION) {
            $this->getResult()->addRequirement(Reason::ANON_CLASS, $node->getLine());
        }
    }

    private function handleClassName($name, $line = -1)
    {
        if ($name !== null) {
            $baseName = baseName(str_replace('\\', '/', $name));
            if ($this->mode & self::MODE_DEPRECATION && $this->isNameSoftReserved($name)) {
                $this->getResult()->addLimit(Reason::SOFT_RESERVED_NAME, $line, null,
                    ['fqn' => $name, 'class' => $baseName]);
            } elseif ($this->mode & self::MODE_REMOVAL && $this->isNameReserved($name)) {
                $this->getResult()->addLimit(Reason::RESERVED_CLASS_NAME, $line, null,
                    ['fqn' => $name, 'class' => $baseName]);
            }
        }
    }

    private function isNameSoftReserved($name)
    {
        return in_array(strtolower(basename(str_replace('\\', '/', $name))),
            array_map('strtolower', self::$softReservedNames));
    }

    private function isNameReserved($name)
    {
        return in_array(strtolower(basename(str_replace('\\', '/', $name))),
            array_map('strtolower', self::$reservedNames));
    }
}
