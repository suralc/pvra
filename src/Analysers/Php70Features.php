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
    private $reservedNames = ['string', 'int', 'float', 'bool', 'null', 'false', 'true'];
    private $softReservedNames = ['object', 'resource', 'mixed', 'numeric'];

    public function enterNode(Node $node)
    {
        if ($node instanceof Node\Stmt\ClassLike) {
            if ($node instanceof Node\Stmt\Class_) {
                $this->detectAndHandlePhp4Ctor($node);
            }
            $this->detectAndHandleReservedNames($node);
        } elseif ($this->isFunctionLike($node)) {
            $this->detectAndHandleReturnTypeDeclaration($node);
        } elseif ($node instanceof Expr\FuncCall) {
            $this->detectAndHandleClassAliasCallToReservedName($node);
        }
        $this->detectAndHandleOperatorAdditions($node);
    }

    private function detectAndHandlePhp4Ctor(Node\Stmt\Class_ $cls)
    {
        if ($this->mode & self::MODE_DEPRECATION) {
            $name = isset($cls->namespacedName) ? $cls->namespacedName->toString() : $cls->name;
            /** @var Node\Stmt\ClassMethod $method */
            foreach ($cls->getMethods() as $method) {
                if (strcasecmp($method->name, $name) === 0) {
                    $this->getResult()->addLimit(Reason::PHP4_CONSTRUCTOR, $method->getLine(), null,
                        ['name' => $method->name]);
                    break;
                }
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
        if ($call->name instanceof Node\Name && strcasecmp('class_alias', self::getLastPartFromName($call->name)) === 0
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

    private function handleClassName($name, $line = -1)
    {
        $baseName = baseName(str_replace('\\', '/', $name));
        if ($this->mode & self::MODE_DEPRECATION && $this->isNameSoftReserved($name)) {
            $this->getResult()->addLimit(Reason::SOFT_RESERVED_NAME, $line, null,
                ['fqn' => $name, 'class' => $baseName]);
        } elseif ($this->mode & self::MODE_REMOVAL && $this->isNameReserved($name)) {
            $this->getResult()->addLimit(Reason::RESERVED_CLASS_NAME, $line, null,
                ['fqn' => $name, 'class' => $baseName]);
        }
    }

    private static function getLastPartFromName(Node\Name $name)
    {
        $parts = $name->parts;
        return end($parts);
    }

    private function isNameSoftReserved($name)
    {
        return in_array(strtolower(basename(str_replace('\\', '/', $name))),
            array_map('strtolower', $this->softReservedNames));
    }

    private function isNameReserved($name)
    {
        return in_array(strtolower(basename(str_replace('\\', '/', $name))),
            array_map('strtolower', $this->reservedNames));
    }
}
