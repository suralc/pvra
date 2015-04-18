<?php
/**
 * Php53Features.php
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
use Pvra\Result\Reason;

/**
 * Class Php53Features
 *
 * Used for the detection of the following elements:
 * * `goto` and Jump constructs
 * * Namespace related features
 * * NowDoc definitions
 * * Definitions of PHP 5.3+ magic methods and their explicit usage
 * * __DIR__ constant
 * * Short ternary
 * * Closure definition using `function() {}` syntax
 * * Late state binding
 *
 * @package Pvra\Analysers
 */
class Php53Features extends LanguageFeatureAnalyser
{
    /**
     * Stores the current in class state of this NodeVisitor.
     * This is required to detect the usage of doc-syntax usaged within class constant
     * definitions.
     *
     * @var bool
     */
    private $inClass = false;
    private $importedNames = [];

    /**
     * @inheritdoc
     */
    public function enterNode(Node $node)
    {
        if ($this->isClassDeclarationStatement($node)) {
            $this->inClass = true;
        }
        $this->detectGotoKeywordAndJumpLabel($node);
        $this->detectNamespaces($node);
        $this->detectNowDoc($node);
        $this->detectNewMagicDefinitions($node);
        $this->detectDocFormatConstantInitializationAndConstOutsideClass($node);
        $this->detectShortHandTernary($node);
        $this->detectClosures($node);
        $this->detectDynamicAccessToStatic($node);
        $this->detectLateStateBinding($node);
    }

    /**
     * @inheritdoc
     */
    public function leaveNode(Node $node)
    {
        if ($this->isClassDeclarationStatement($node)) {
            $this->inClass = false;
        }
    }

    /**
     * @param \PhpParser\Node $node
     * @return bool
     */
    private function isClassDeclarationStatement(Node $node)
    {
        return $node instanceof Node\Stmt\Class_ || $node instanceof Node\Stmt\Interface_ || $node instanceof Node\Stmt\Trait_;
    }

    /**
     * @param \PhpParser\Node $node
     */
    private function detectGotoKeywordAndJumpLabel(Node $node)
    {
        if ($node instanceof Node\Stmt\Goto_) {
            $this->getResult()->addRequirement(
                Reason::GOTO_KEYWORD,
                $node->getLine(),
                null,
                ['name' => $node->name]
            );
        } elseif ($node instanceof Node\Stmt\Label) {
            $this->getResult()->addRequirement(
                Reason::JUMP_LABEL,
                $node->getLine(),
                null,
                ['name' => $node->name]
            );
        }
    }

    /**
     * @param \PhpParser\Node $node
     */
    private function detectNamespaces(Node $node)
    {
        if ($node instanceof Node\Stmt\Namespace_) {
            $this->getResult()->addRequirement(
                Reason::NAMESPACE_DECLERATION,
                $node->getLine(),
                null,
                ['name' => isset($node->name) ? $node->name->toString() : '::global::']
            );
        } elseif ($node instanceof Node\Scalar\MagicConst\Namespace_) {
            $this->getResult()->addRequirement(Reason::NAMESPACE_MAGIC_CONSTANT, $node->getLine());
        } elseif ($node instanceof Node\Stmt\Use_ && $node->type === Node\Stmt\Use_::TYPE_NORMAL) {
            $this->getResult()->addRequirement(
                Reason::NAMESPACE_IMPORT,
                $node->getLine(),
                null,
                ['import_count' => count($node->uses)]
            );
        }
    }

    /**
     * @param \PhpParser\Node $node
     */
    private function detectNowDoc(Node $node)
    {
        if ($node->hasAttribute('isNowDoc') && $node instanceof Node\Scalar\String) {
            $this->getResult()->addRequirement(Reason::NOWDOC_LITERAL, $node->getLine());
        }
    }

    /**
     * @param \PhpParser\Node $node
     */
    private function detectNewMagicDefinitions(Node $node)
    {
        if (($node instanceof Node\Stmt\ClassMethod && $node->isPublic())
            || ($node instanceof Node\Expr\MethodCall || $node instanceof Node\Expr\StaticCall)
            && is_string($node->name)
        ) {
            if (strcasecmp($node->name, '__callStatic') === 0) {
                $this->getResult()->addRequirement(Reason::CALLSTATIC_MAGIC_METHOD, $node->getLine());
            } elseif (strcasecmp($node->name, '__invoke') === 0) {
                $this->getResult()->addRequirement(Reason::INVOKE_MAGIC_METHOD, $node->getLine());
            }
        } elseif ($node instanceof Node\Scalar\MagicConst\Dir) {
            $this->getResult()->addRequirement(Reason::DIR_MAGIC_CONSTANT, $node->getLine());
        }
    }

    /**
     * @param \PhpParser\Node $node
     */
    private function detectDocFormatConstantInitializationAndConstOutsideClass(Node $node)
    {
        if ($node instanceof Node\Stmt\Const_) {
            if (!$this->inClass) {
                $this->getResult()->addRequirement(Reason::CONST_KEYWORD_OUTSIDE_CLASS, $node->getLine());
            }
        }
        if ($node instanceof Node\Stmt\Const_ || $node instanceof Node\Stmt\ClassConst) {
            foreach ($node->consts as $const) {
                if ($const->value->hasAttribute('isDocSyntax')) {
                    $this->getResult()->addRequirement(
                        Reason::CONST_KEYWORD_DOC_SYNTAX,
                        $const->value->getLine(),
                        null,
                        ['name' => $const->name]
                    );
                }
            }
        }
    }

    /**
     * @param \PhpParser\Node $node
     */
    private function detectShortHandTernary(Node $node)
    {
        if ($node instanceof Node\Expr\Ternary && $node->if === null) {
            $this->getResult()->addRequirement(Reason::SHORT_TERNARY, $node->getLine());
        }
    }

    /**
     * @param \PhpParser\Node $node
     */
    private function detectClosures(Node $node)
    {
        if ($node instanceof Node\Expr\Closure) {
            $this->getResult()->addRequirement(Reason::CLOSURE_DECLARATION, $node->getLine());
        }
    }

    /**
     * @param \PhpParser\Node $node
     */
    private function detectDynamicAccessToStatic(Node $node)
    {
        if (($node instanceof Node\Expr\StaticPropertyFetch || $node instanceof Node\Expr\StaticCall)
            && $node->class instanceof Node\Expr
        ) {
            $this->getResult()->addRequirement(Reason::DYNAMIC_ACCESS_TO_STATIC, $node->getLine());
        }
    }

    /**
     * @param \PhpParser\Node $node
     */
    private function detectLateStateBinding(Node $node)
    {
        if (($node instanceof Node\Expr\StaticPropertyFetch || $node instanceof Node\Expr\StaticCall || $node instanceof Node\Expr\ClassConstFetch)
            && $node->class instanceof Node\Name && strcasecmp($node->class->toString(), 'static') === 0
        ) {
            $this->getResult()->addRequirement(Reason::LATE_STATE_BINDING_USING_STATIC, $node->getLine());
        }
    }
}
