<?php
namespace Pvra\PhpParser\AnalysingNodeWalkers;


use PhpParser\Node;
use PhpParser\Node\Name;
use Pvra\PhpParser\RequirementAnalyserAwareInterface;
use Pvra\RequirementAnalysis\RequirementAnalyser;
use Pvra\RequirementAnalysis\Result\RequirementReason;

class LibraryAdditionsNodeWalker extends LanguageFeatureAnalyser implements RequirementAnalyserAwareInterface
{
    private $data;

    /**
     * @param \Pvra\RequirementAnalysis\RequirementAnalyser $requirementAnalyser
     * @param string $sourceDataFile
     */
    public function __construct(RequirementAnalyser $requirementAnalyser = null, $sourceDataFile = null)
    {
        parent::__construct($requirementAnalyser);
        if ($sourceDataFile === null) {
            $sourceDataFile = __DIR__ . '/../../../data/changes.php';
        }

        if (!file_exists($sourceDataFile) || !is_readable($sourceDataFile)) {
            throw new \InvalidArgumentException(sprintf('The file "%s" does not exist or is not readable',
                $sourceDataFile));
        }

        $this->data = include $sourceDataFile;
    }

    public function enterNode(Node $node)
    {
        // TODO: refactor this, to avoid code duplication
        // direct class calls
        if ($node instanceof Node\Expr\New_ || $node instanceof Node\Expr\StaticCall) {
            if (count($node->class->parts) === 1) {
                if ($this->hasClassVersionRequirement($node->class->parts[0])) {
                    $this->getOwningAnalyser()->getResult()->addArbitraryRequirement(
                        $this->getClassVersionRequirement($node->class->parts[0]),
                        $node->getLine(),
                        sprintf('The "%s" class was introduced in php %s', $node->class->parts[0],
                            $this->data['classes-added'][ $node->class->parts[0] ]),
                        RequirementReason::CLASS_PRESENCE_CHANGE
                    );
                }
            }
        } elseif ($node instanceof Node\Stmt\Function_
            || $node instanceof Node\Stmt\ClassMethod
            || $node instanceof Node\Expr\Closure
        ) {
            if (!empty($node->params)) {
                foreach ($node->params as $param) {
                    if (count($param->type->parts) === 1
                        && $this->hasClassVersionRequirement($param->type->getLast())
                    ) {
                        $this->getOwningAnalyser()->getResult()->addArbitraryRequirement(
                            $this->getClassVersionRequirement($param->type->getLast()),
                            $param->getLine(),
                            sprintf('The "%s" class was introduced in php %s', $param->type->parts[0],
                                $this->data['classes-added'][ $param->type->getLast() ]),
                            RequirementReason::CLASS_PRESENCE_CHANGE
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
                        sprintf('The "%s" class was introduced in php %s', $name->getLast(),
                            $this->data['classes-added'][ $name->getLast() ]),
                        RequirementReason::CLASS_PRESENCE_CHANGE
                    );
                }
            }
        } elseif ($node instanceof Node\Expr\FuncCall) {
            // core functions are not namespaced:
            if (count($node->name->parts) === 1 && $this->hasFunctionVersionRequirement($node->name->getLast())) {
                $this->getResult()->addArbitraryRequirement(
                    $this->getFunctionVersionRequirement($node->name->getLast()),
                    $node->getLine(),
                    sprintf('The "%s" function was introduced in php %s', $node->name->getLast(),
                        $this->getFunctionVersionRequirement($node->name->getLast())),
                    RequirementReason::FUNCTION_PRESENCE_CHANGE
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
        if (!isset($this->data['functions-added'])) {
            throw new \RuntimeException('Invalid data file format');
        }

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
        if (!isset($this->data['classes-added'])) {
            throw new \RuntimeException('Invalid data file format');
        }

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
}