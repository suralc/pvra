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
    private $reservedNames = ['string', 'int', 'float', 'bool'];

    public function enterNode(Node $node)
    {
        if ($node instanceof Node\Stmt\Class_ || $node instanceof Node\Stmt\Interface_ || $node instanceof Node\Stmt\Trait_) {
            if ($this->mode & self::MODE_DEPRECATION) {
                $name = isset($node->namespacedName) ? $node->namespacedName->toString() : $node->name;
                /** @var Node\Stmt\ClassMethod $method */
                foreach ($node->getMethods() as $method) {
                    if (strcasecmp($method->name, $name) === 0) {
                        $this->getResult()->addLimit(Reason::PHP4_CONSTRUCTOR, $method->getLine(), null,
                            ['name' => $method->name]);
                        break;
                    }
                }
            }
            if ($this->mode & self::MODE_REMOVAL && $this->isClassNameReserved($node->name)) {
                $this->getResult()->addLimit(Reason::RESERVED_CLASS_NAME, $node->getLine(), null,
                    ['class' => $node->name]);
            }
        }
        if ($this->mode & self::MODE_ADDITION) {
            if ($node instanceof Expr\BinaryOp\Coalesce) {
                $this->getResult()->addRequirement(Reason::COALESCE_OPERATOR, $node->getLine());
            }
            if ($node instanceof Expr\BinaryOp\Spaceship) {
                $this->getResult()->addRequirement(Reason::SPACESHIP_OPERATOR, $node->getLine());
            }
            if (($node instanceof Node\Stmt\ClassMethod
                    || $node instanceof Node\Stmt\Function_
                    || $node instanceof Expr\Closure)
                && $node->returnType !== null
            ) {
                $this->getResult()->addRequirement(Reason::RETURN_TYPE, $node->getLine());
            }
        }
        if ($this->mode & self::MODE_REMOVAL && $node instanceof Expr\FuncCall && $node->name instanceof Node\Name
            && strcasecmp('class_alias', self::getLastPartFromName($node->name)) === 0
        ) {
            if (isset($node->args[1]) && $node->args[1]->value instanceof Node\Scalar\String_
                && $this->isClassNameReserved($node->args[1]->value->value)
            ) {
                $this->getResult()->addLimit(Reason::RESERVED_CLASS_NAME, $node->getLine(), null,
                    ['class' => $node->args[1]->value->value]);
            }
        }
    }

    private function isClassNameReserved($name)
    {
        return in_array(strtolower(basename(str_replace('\\', '/', $name))),
            array_map('strtolower', $this->reservedNames));
    }

    private static function getLastPartFromName(Node\Name $name)
    {
        $parts = $name->parts;
        return end($parts);
    }
}
