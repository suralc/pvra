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
use Pvra\Analyser;
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
class LibraryAdditions extends LanguageFeatureAnalyser implements AnalyserAwareInterface
{
    /**
     * @var array
     */
    private $data;

    /**
     * @param \Pvra\Analyser $requirementAnalyser
     * @param string|array $libraryData
     */
    public function __construct(Analyser $requirementAnalyser = null, $libraryData = null)
    {
        parent::__construct($requirementAnalyser);

        if ($libraryData === null) {
            $libraryData = __DIR__ . '/../../data/changes.php';
        }
        if (is_string($libraryData)) {
            if (!file_exists($libraryData) || !is_readable($libraryData)) {
                throw new \InvalidArgumentException(sprintf('The file "%s" does not exist or is not readable',
                    $libraryData));
            }

            $this->data = include $libraryData;
        } elseif (is_array($libraryData)) {
            $this->data = $libraryData;
        } else {
            throw new \InvalidArgumentException(sprintf('The $libraryData parameter has to be a string or an array. %s given.',
                gettype($libraryData) === 'object' ? get_class($libraryData) : gettype($libraryData)));
        }

        $this->ensureAdditionsDataIntegrity();
    }

    /**
     * @inheritdoc
     */
    public function enterNode(Node $node)
    {
        // direct class calls
        if ($node instanceof Node\Expr\New_ || $node instanceof Node\Expr\StaticCall || $node instanceof Node\Expr\ClassConstFetch) {
            if (count($node->class->parts) === 1
                && is_string($req = $this->getClassVersionRequirement($node->class->parts[0]))
            ) {
                $this->getOwningAnalyser()->getResult()->addArbitraryRequirement(
                    $req,
                    $node->getLine(),
                    null,
                    Reason::CLASS_PRESENCE_CHANGE,
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
                        && is_string(($req = $this->getClassVersionRequirement($param->type->getLast())))
                    ) {
                        $this->getOwningAnalyser()->getResult()->addArbitraryRequirement(
                            $req,
                            $param->getLine(),
                            null,
                            Reason::CLASS_PRESENCE_CHANGE,
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
                if (count($name->parts) === 1 && is_string($req = $this->getClassVersionRequirement($name->getLast()))) {
                    $this->getOwningAnalyser()->getResult()->addArbitraryRequirement(
                        $req,
                        $node->getLine(),
                        null,
                        Reason::CLASS_PRESENCE_CHANGE,
                        ['className' => $name->getLast()]
                    );
                }
            }
        } elseif ($node instanceof Node\Expr\FuncCall) {
            // core functions are not namespaced, has to be redone to allow extension support
            if (count($node->name->parts) === 1 && is_string(($req = $this->getFunctionVersionRequirement($node->name->getLast())))) {
                $this->getResult()->addArbitraryRequirement(
                    $req,
                    $node->getLine(),
                    null,
                    Reason::FUNCTION_PRESENCE_CHANGE,
                    ['functionName' => $node->name->getLast()]
                );
            }
        }
    }

    /**
     * @param string $name
     * @return bool
     */
    private function getFunctionVersionRequirement($name)
    {
        if (isset($this->data['functions-added'][ $name ])) {
            return $this->data['functions-added'][ $name ];
        }

        return false;
    }

    /**
     * @param string $name
     * @return bool
     */
    private function getClassVersionRequirement($name)
    {
        if (isset($this->data['classes-added'][ $name ])) {
            return $this->data['classes-added'][ $name ];
        }

        return false;
    }

    /**
     *
     */
    private function ensureAdditionsDataIntegrity()
    {
        if (empty($this->data)) {
            throw new \LogicException('No valid, non-empty library information has been loaded. This should have happened in the constructor.');
        }

        if (!isset($this->data['classes-added']) || !is_array($this->data['classes-added'])) {
            throw new \RuntimeException('Valid library data must have a "classes-added" list.');
        }

        if (!isset($this->data['functions-added']) || !is_array($this->data['functions-added'])) {
            throw new \RuntimeException('Valid library data must have a "functions-added" list.');
        }
    }
}
