<?php
/**
 * LibraryChanges.php
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
use Pvra\InformationProvider\LibraryInformation;
use Pvra\InformationProvider\LibraryInformationAwareInterface;
use Pvra\InformationProvider\LibraryInformationInterface;
use Pvra\Result\Reason;

/**
 * Class LibraryChanges
 *
 * @package Pvra\Analysers
 */
class LibraryChanges extends LanguageFeatureAnalyser implements AnalyserAwareInterface, LibraryInformationAwareInterface
{
    /**
     * @var LibraryInformation
     */
    private $information;

    /**
     * @inheritdoc
     * @param array $options
     * @param \Pvra\Analyser $analyser
     * @param \Pvra\InformationProvider\LibraryInformation $information
     */
    public function __construct(array $options = [], Analyser $analyser = null, LibraryInformation $information = null)
    {
        parent::__construct($options, $analyser);

        if ($information !== null) {
            $this->information = $information;
        }
    }

    /**
     * @param \Pvra\InformationProvider\LibraryInformationInterface $libInfo
     * @return $this
     */
    public function setLibraryInformation(LibraryInformationInterface $libInfo)
    {
        $this->information = $libInfo;

        return $this;
    }

    /**
     * @param \Pvra\InformationProvider\LibraryInformationInterface $libInfo
     * @return $this
     */
    public function addLibraryInformation(LibraryInformationInterface $libInfo)
    {
        $this->getLibraryInformation()->mergeWith($libInfo);

        return $this;
    }

    /**
     * @return LibraryInformationInterface
     */
    public function getLibraryInformation()
    {
        if ($this->information === null) {
            if (($path = $this->getOption('libraryDataPath')) !== null) {
                return $this->information = LibraryInformation::createFromFile($path);
            }
            $this->information = LibraryInformation::createWithDefaults();
        }
        return $this->information;
    }

    /**
     * Prepare a name
     *
     * If the first argument is an instance of `PhpParser\Node\Name` its string representation
     * will be returned as first element of the result array. The value retrieved via `PhpParser\Node\Name::getLine()`
     * will be used as second element.
     * If the first parameter is not an instance of `PhpParser\Node\Name` it will be casted to string and returned alongside
     * with the value given for the second parameter as line
     * @param \PhpParser\Node\Name|string $name
     * @param int $line Only used if the $name parameter is not an instance of `PhpParser\Node\Name`
     * @return array
     */
    private function prepareNameAndLine($name, $line = -1)
    {
        if ($name instanceof Node\Name) {
            $line = $name->getLine();
            $name = $name->toString();
        } else {
            $name = (string)$name;
        }

        return [$name, $line];
    }

    /**
     * @param \PhpParser\Node\Name|string $name
     * @param int $line
     */
    private function handleClassName($name, $line = -1)
    {
        list($name, $line) = $this->prepareNameAndLine($name, $line);
        $info = $this->getLibraryInformation()->getClassInfo($name);
        if ($this->mode & self::MODE_ADDITION && $info['addition'] !== null) {
            $this->getResult()->addArbitraryRequirement(
                $info['addition'],
                $line,
                null,
                Reason::LIB_CLASS_ADDITION,
                ['className' => $name]
            );
        }
        if ($this->mode & self::MODE_DEPRECATION && $info['deprecation'] !== null) {
            $this->getResult()->addArbitraryLimit(
                $info['deprecation'],
                $line,
                null,
                Reason::LIB_CLASS_DEPRECATION,
                ['className' => $name]
            );
        }
        if ($this->mode & self::MODE_REMOVAL && $info['removal'] !== null) {
            $this->getResult()->addArbitraryLimit(
                $info['removal'],
                $line,
                null,
                Reason::LIB_CLASS_REMOVAL,
                ['className' => $name]
            );
        }
    }

    /**
     * @param \PhpParser\Node\Name|string $name
     * @param int $line
     */
    private function handleFunctionName($name, $line = -1)
    {
        list($name, $line) = $this->prepareNameAndLine($name, $line);
        $info = $this->getLibraryInformation()->getFunctionInfo($name);
        if ($this->mode & self::MODE_ADDITION && $info['addition'] !== null) {
            $this->getResult()->addArbitraryRequirement(
                $info['addition'],
                $line,
                null,
                Reason::LIB_FUNCTION_ADDITION,
                ['functionName' => $name]
            );
        }
        if ($this->mode & self::MODE_DEPRECATION && $info['deprecation'] !== null) {
            $this->getResult()->addArbitraryLimit(
                $info['deprecation'],
                $line,
                null,
                Reason::LIB_FUNCTION_DEPRECATION,
                ['functionName' => $name]
            );
        }
        if ($this->mode & self::MODE_REMOVAL && $info['removal'] !== null) {
            $this->getResult()->addArbitraryLimit(
                $info['removal'],
                $line,
                null,
                Reason::LIB_FUNCTION_REMOVAL,
                ['functionName' => $name]
            );
        }
    }

    /**
     * @param \PhpParser\Node\Name|string $name
     * @param int $line
     */
    private function handleConstantName($name, $line = -1)
    {
        list($name, $line) = $this->prepareNameAndLine($name, $line);
        $info = $this->getLibraryInformation()->getConstantInfo($name);
        if (($this->mode & self::MODE_ADDITION) && $info['addition'] !== null) {
            $this->getResult()->addArbitraryRequirement(
                $info['addition'],
                $line,
                null,
                Reason::LIB_CONSTANT_ADDITION,
                ['constantName' => $name]
            );
        }
        if (($this->mode & self::MODE_DEPRECATION) && $info['deprecation'] !== null) {
            $this->getResult()->addArbitraryLimit(
                $info['deprecation'],
                $line,
                null,
                Reason::LIB_CONSTANT_DEPRECATION,
                ['constantName' => $name]
            );
        }
        if (($this->mode & self::MODE_REMOVAL) && $info['removal'] !== null) {
            $this->getResult()->addArbitraryLimit(
                $info['removal'],
                $line,
                null,
                Reason::LIB_CONSTANT_REMOVAL,
                ['constantName' => $name]
            );
        }
    }

    /**
     * @inheritdoc
     * @param \PhpParser\Node $node
     * @return null|\PhpParser\Node|void
     */
    public function enterNode(Node $node)
    {
        // direct class calls
        if ($node instanceof Node\Expr\New_ || $node instanceof Node\Expr\StaticCall
            || $node instanceof Node\Expr\ClassConstFetch || $node instanceof Node\Expr\StaticPropertyFetch
        ) {
            if ($node->class instanceof Node\Name) {
                $this->handleClassName($node->class);
            }
            if ($node instanceof Node\Expr\ClassConstFetch) {
                $this->handleConstantName($node->name, $node->getLine());
            }
        } elseif ($node instanceof Node\Stmt\Function_
            || $node instanceof Node\Stmt\ClassMethod
            || $node instanceof Node\Expr\Closure
        ) {
            if (!empty($node->params)) {
                foreach ($node->params as $param) {
                    if (isset($param->type) && !is_string($param->type)) {
                        $this->handleClassName($param->type);
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
                $this->handleClassName($name);
            }
        } elseif ($node instanceof Node\Expr\FuncCall) {
            $this->handleFunctionName($node->name);
        } elseif ($node instanceof Node\Expr\ConstFetch) {
            $this->handleConstantName($node->name);
        }
    }
}
