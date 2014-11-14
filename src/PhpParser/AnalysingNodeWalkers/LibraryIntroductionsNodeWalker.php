<?php
namespace Pvra\PhpParser\AnalysingNodeWalkers;


use PhpParser\Node;
use PhpParser\Node\Name;
use Pvra\PhpParser\RequirementAnalyserAwareInterface;

class LibraryIntroductionsNodeWalker extends LanguageFeatureAnalyser implements RequirementAnalyserAwareInterface
{
    private $data;

    public function __construct($sourceDataFile = null)
    {
        if ($sourceDataFile === null) {
            $sourceDataFile = __DIR__ . '/../../../data/changes.php';
        }

        if (!file_exists($sourceDataFile) || !is_readable($sourceDataFile)) {
            throw new \InvalidArgumentException;
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
                    $this->getOwningAnalyser()->getResult()->addRequirement(
                        $this->getClassVersionRequirement($node->class->parts[0]),
                        [
                            'file' => $this->getOwningAnalyser()->getResult()->getAnalysisTargetId(),
                            'line' => $node->getLine()
                        ],
                        sprintf('The "%s" class was introduced in php %s', $node->class->parts[0],
                            $this->data['classes-added'][ $node->class->parts[0] ])
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
                        && $this->hasClassVersionRequirement($param->type->parts[0])
                    ) {
                        $this->getOwningAnalyser()->getResult()->addRequirement(
                            $this->getClassVersionRequirement($param->type->parts[0]),
                            [
                                'file' => $this->getOwningAnalyser()->getResult()->getAnalysisTargetId(),
                                'line' => $param->getLine()
                            ],
                            sprintf('The "%s" class was introduced in php %s', $param->type->parts[0],
                                $this->data['classes-added'][ $param->type->parts[0] ])
                        );
                    }
                }
            }
        } elseif ($node instanceof Node\Stmt\Class_
            || $node instanceof Node\Stmt\Interface_
        ) {

            // TODO: fix interface extends
            $names = [];
            if (!empty($node->implements)) {
                $names += $node->implements;
            }
            if ($node->extends instanceof Name) {
                $names[] = $node->extends;
            }

            foreach ($names as $name) {
                if (count($name->parts) === 1) {
                    if ($this->hasClassVersionRequirement($name->parts[0])) {
                        $this->getOwningAnalyser()->getResult()->addRequirement(
                            $this->getClassVersionRequirement($name->parts[0]),
                            [
                                'file' => $this->getOwningAnalyser()->getResult()->getAnalysisTargetId(),
                                'line' => $node->getLine()
                            ],
                            sprintf('The "%s" class was introduced in php %s', $name->parts[0],
                                $this->data['classes-added'][ $name->parts[0] ])
                        );
                    }
                }
            }
        }
    }

    /**
     * @param string $name
     * @return bool
     */
    private function hasClassVersionRequirement($name)
    {
        if (!isset($this->data['classes'])) {
            throw new \RuntimeException('Invalid data file format');
        }

        return isset($this->data['classes-added'][ $name ]);
    }

    private function getClassVersionRequirement($name)
    {
        if ($this->hasClassVersionRequirement($name)) {
            return $this->data['classes-added'][ $name ];
        }

        return false;
    }
}
