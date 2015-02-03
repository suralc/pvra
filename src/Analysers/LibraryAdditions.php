<?php
/**
 * LibraryAdditions.php
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
 * @author     suralc <thesurwaveing@gmail.com>
 * @license    http://opensource.org/licenses/MIT  MIT
 */
namespace Pvra\Analysers;


use PhpParser\Node;
use PhpParser\Node\Name;
use Pvra\AnalyserAwareInterface;
use Pvra\Result\Reason;

/**
 * Class LibraryAdditions
 *
 * This class may be used in conjunction with a library data provider (at this time it's just an array) to detect
 * availability of a class or a function.
 *
 * @package Pvra\PhpParser\Analysers
 */
class LibraryAdditions extends LibraryChanges implements AnalyserAwareInterface
{
    /**
     * @inheritdoc
     */
    public function enterNode(Node $node)
    {
        // direct class calls
        if ($node instanceof Node\Expr\New_ || $node instanceof Node\Expr\StaticCall || $node instanceof Node\Expr\ClassConstFetch) {
            if (count($node->class->parts) === 1
                && ($req = $this->getLibraryInformation()->getClassInfo($node->class->parts[0])['addition'])
            ) {
                $this->getOwningAnalyser()->getResult()->addArbitraryRequirement(
                    $req,
                    $node->getLine(),
                    null,
                    Reason::LIB_CLASS_ADDITION,
                    ['className' => $node->class->parts[0]]
                );
            }
        } elseif ($node instanceof Node\Stmt\Function_
            || $node instanceof Node\Stmt\ClassMethod
            || $node instanceof Node\Expr\Closure
        ) {
            if (!empty($node->params)) {
                foreach ($node->params as $param) {
                    if (isset($param->type) && !is_string($param->type)
                        && count($param->type->parts) === 1
                        && ($req = $this->getLibraryInformation()->getClassInfo($param->type->getLast())['addition'])
                    ) {
                        $this->getOwningAnalyser()->getResult()->addArbitraryRequirement(
                            $req,
                            $param->getLine(),
                            null,
                            Reason::LIB_CLASS_ADDITION,
                            ['className' => $param->type->parts[0]]
                        );
                    }
                }
            }
        } elseif ($node instanceof Node\Stmt\Class_
            || $node instanceof Node\Stmt\Interface_
        ) {
            $names = [];
            if (!empty($node->implements)) {
                $names += $node->implements;
            }
            if (!empty($node->extends)) {
                if ($node->extends instanceof Name) {
                    $names[] = $node->extends;
                } else {
                    $names += $node->extends;
                }
            }

            foreach ($names as $name) {
                if (count($name->parts) === 1 && ($req = $this->getLibraryInformation()->getClassInfo($name->getLast())['addition'])) {
                    $this->getOwningAnalyser()->getResult()->addArbitraryRequirement(
                        $req,
                        $node->getLine(),
                        null,
                        Reason::LIB_CLASS_ADDITION,
                        ['className' => $name->getLast()]
                    );
                }
            }
        } elseif ($node instanceof Node\Expr\FuncCall) {
            // core functions are not namespaced, has to be redone to allow extension support
            if (count($node->name->parts) === 1 && ($req = $this->getLibraryInformation()->getFunctionInfo($node->name->getLast())['addition'])) {
                $this->getResult()->addArbitraryRequirement(
                    $req,
                    $node->getLine(),
                    null,
                    Reason::LIB_FUNCTION_ADDITION,
                    ['functionName' => $node->name->getLast()]
                );
            }
        }
    }
}
