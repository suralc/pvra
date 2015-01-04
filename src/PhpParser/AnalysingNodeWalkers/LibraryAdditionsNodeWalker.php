<?php
/**
 * LibraryAdditionsNodeWalker.php
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
namespace Pvra\PhpParser\AnalysingNodeWalkers;


use PhpParser\Node;
use PhpParser\Node\Name;
use Pvra\PhpParser\RequirementAnalyserAwareInterface;
use Pvra\RequirementAnalysis\RequirementAnalyser;
use Pvra\RequirementAnalysis\Result\RequirementReason;

/**
 * Class LibraryAdditionsNodeWalker
 *
 * This class may be used in conjunction with a library data provider (at this time it's just an array) to detect
 * availability of a class or a function.
 *
 * @package Pvra\PhpParser\AnalysingNodeWalkers
 */
class LibraryAdditionsNodeWalker extends LanguageFeatureAnalyser implements RequirementAnalyserAwareInterface
{
    /**
     * @var array
     */
    private $data;

    /**
     * @param \Pvra\RequirementAnalysis\RequirementAnalyser $requirementAnalyser
     * @param string|array $libraryData
     */
    public function __construct(RequirementAnalyser $requirementAnalyser = null, $libraryData = null)
    {
        parent::__construct($requirementAnalyser);

        if ($libraryData === null) {
            $libraryData = __DIR__ . '/../../../data/changes.php';
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
        if ($node instanceof Node\Expr\New_ || $node instanceof Node\Expr\StaticCall) {
            if (count($node->class->parts) === 1) {
                if ($this->hasClassVersionRequirement($node->class->parts[0])) {
                    $this->getOwningAnalyser()->getResult()->addArbitraryRequirement(
                        $this->getClassVersionRequirement($node->class->parts[0]),
                        $node->getLine(),
                        null,
                        RequirementReason::CLASS_PRESENCE_CHANGE,
                        ['className' => $node->class->parts[0]]
                    );
                }
            }
        } elseif ($node instanceof Node\Stmt\Function_
            || $node instanceof Node\Stmt\ClassMethod
            || $node instanceof Node\Expr\Closure
        ) {
            if (!empty($node->params)) {
                foreach ($node->params as $param) {
                    if (isset($param->type) && !is_string($param->type)
                        && count($param->type->parts) === 1
                        && $this->hasClassVersionRequirement($param->type->getLast())
                    ) {
                        $this->getOwningAnalyser()->getResult()->addArbitraryRequirement(
                            $this->getClassVersionRequirement($param->type->getLast()),
                            $param->getLine(),
                            null,
                            RequirementReason::CLASS_PRESENCE_CHANGE,
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
                if (count($name->parts) === 1 && $this->hasClassVersionRequirement($name->getLast())) {
                    $this->getOwningAnalyser()->getResult()->addArbitraryRequirement(
                        $this->getClassVersionRequirement($name->getLast()),
                        $node->getLine(),
                        null,
                        RequirementReason::CLASS_PRESENCE_CHANGE,
                        ['className' => $name->getLast()]
                    );
                }
            }
        } elseif ($node instanceof Node\Expr\FuncCall) {
            // core functions are not namespaced:
            if (count($node->name->parts) === 1 && $this->hasFunctionVersionRequirement($node->name->getLast())) {
                $this->getResult()->addArbitraryRequirement(
                    $this->getFunctionVersionRequirement($node->name->getLast()),
                    $node->getLine(),
                    null,
                    RequirementReason::FUNCTION_PRESENCE_CHANGE,
                    ['functionName' => $node->name->getLast()]
                );
            }
        }
    }

    /**
     * @param string $name
     * @return bool
     */
    private function hasFunctionVersionRequirement($name)
    {
        return isset($this->data['functions-added'][ $name ]);
    }

    /**
     * @param string $name
     * @return bool
     */
    private function getFunctionVersionRequirement($name)
    {
        if ($this->hasFunctionVersionRequirement($name)) {
            return $this->data['functions-added'][ $name ];
        }

        return false;
    }

    /**
     * @param string $name
     * @return bool
     */
    private function hasClassVersionRequirement($name)
    {
        return isset($this->data['classes-added'][ $name ]);
    }

    /**
     * @param string $name
     * @return bool
     */
    private function getClassVersionRequirement($name)
    {
        if ($this->hasClassVersionRequirement($name)) {
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
