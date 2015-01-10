<?php
/**
 * Php53LanguageFeatureNodeWalker.php
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
use Pvra\RequirementAnalysis\Result\RequirementReason;

/**
 * Class Php53LanguageFeatureNodeWalker
 *
 * @package Pvra\PhpParser\AnalysingNodeWalkers
 */
class Php53LanguageFeatureNodeWalker extends LanguageFeatureAnalyser
{
    private $inClass = false;
    private $importedNames = [];

    /**
     * @inheritdoc
     */
    public function enterNode(Node $node)
    {
        if ($node instanceof Node\Stmt\Class_
            || $node instanceof Node\Stmt\Interface_
            || $node instanceof Node\Stmt\Trait_
        ) {
            $this->inClass = true;
        } /*elseif ($node instanceof Node\Stmt\Use_) { // required in ::detectNamespaceSeperator
            foreach ($node->uses as $use) {
                $this->importedNames[] = $use->name->toString();
            }
        }*/
        $this->detectGotoKeywordAndJumpLabel($node);
        $this->detectNamespaces($node);
        $this->detectNowDoc($node);
        $this->detectNewMagicMethods($node);
        $this->detectDocFormatConstantInitializationAndConstOutsideClass($node);
        $this->detectShortHandTernary($node);
        $this->detectClosures($node);
        $this->detectDynamicAccessToStatic($node);
        $this->detectLateStateBinding($node);
        //$this->detectNamespaceSeparator($node);
    }

    public function leaveNode(Node $node)
    {
        if ($node instanceof Node\Stmt\Class_
            || $node instanceof Node\Stmt\Interface_
            || $node instanceof Node\Stmt\Trait_
        ) {
            $this->inClass = false;
        }
    }

    /**
     * @param \PhpParser\Node $node
     */
    private function detectGotoKeywordAndJumpLabel(Node $node)
    {
        if ($node instanceof Node\Stmt\Goto_) {
            $this->getResult()->addRequirement(
                RequirementReason::GOTO_KEYWORD,
                $node->getLine(),
                null,
                ['name' => $node->name]
            );
        } elseif ($node instanceof Node\Stmt\Label) {
            $this->getResult()->addRequirement(
                RequirementReason::JUMP_LABEL,
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
                RequirementReason::NAMESPACE_DECLERATION,
                $node->getLine(),
                null,
                ['name' => isset($node->name) ? $node->name->toString() : '::global::']
            );
        } elseif ($node instanceof Node\Scalar\MagicConst\Namespace_) {
            $this->getResult()->addRequirement(RequirementReason::NAMESPACE_MAGIC_CONSTANT, $node->getLine());
        } elseif ($node instanceof Node\Stmt\Use_ && $node->type === Node\Stmt\Use_::TYPE_NORMAL) {
            $this->getResult()->addRequirement(
                RequirementReason::NAMESPACE_IMPORT,
                $node->getLine(),
                null,
                ['import_count' => count($node->uses)]
            );
        }
    }

    private function detectNowDoc(Node $node)
    {
        if ($node->hasAttribute('isNowDoc') && $node instanceof Node\Scalar\String) {
            $this->getResult()->addRequirement(RequirementReason::NOWDOC_LITERAL, $node->getLine());
        }
    }

    private function detectNewMagicMethods(Node $node)
    {
        if (($node instanceof Node\Stmt\ClassMethod && $node->isPublic())
            || ($node instanceof Node\Expr\MethodCall || $node instanceof Node\Expr\StaticCall)
            && is_string($node->name)
        ) {
            if (strcasecmp($node->name, '__callStatic') === 0) {
                $this->getResult()->addRequirement(RequirementReason::CALLSTATIC_MAGIC_METHOD, $node->getLine());
            } elseif (strcasecmp($node->name, '__invoke') === 0) {
                $this->getResult()->addRequirement(RequirementReason::INVOKE_MAGIC_METHOD, $node->getLine());
            }
        }
    }

    private function detectDocFormatConstantInitializationAndConstOutsideClass(Node $node)
    {
        if ($node instanceof Node\Stmt\Const_) {
            if (!$this->inClass) {
                $this->getResult()->addRequirement(RequirementReason::CONST_KEYWORD_OUTSIDE_CLASS, $node->getLine());
            }
        }
        if ($node instanceof Node\Stmt\Const_ || $node instanceof Node\Stmt\ClassConst) {
            foreach ($node->consts as $const) {
                if ($const->value->hasAttribute('isDocSyntax')) {
                    $this->getResult()->addRequirement(
                        RequirementReason::CONST_KEYWORD_DOC_SYNTAX,
                        $const->value->getLine(),
                        null,
                        ['name' => $const->name]
                    );
                }
            }
        }
    }

    private function detectShortHandTernary(Node $node)
    {
        if ($node instanceof Node\Expr\Ternary && $node->if === null) {
            $this->getResult()->addRequirement(RequirementReason::SHORT_TERNARY, $node->getLine());
        }
    }

    private function detectClosures(Node $node)
    {
        if ($node instanceof Node\Expr\Closure) {
            $this->getResult()->addRequirement(RequirementReason::CLOSURE_DECLARATION, $node->getLine());
        }
    }

    private function detectDynamicAccessToStatic(Node $node)
    {
        if (($node instanceof Node\Expr\StaticPropertyFetch || $node instanceof Node\Expr\StaticCall)
            && $node->class instanceof Node\Expr
        ) {
            $this->getResult()->addRequirement(RequirementReason::DYNAMIC_ACCESS_TO_STATIC, $node->getLine());
        }
    }

    private function detectLateStateBinding(Node $node)
    {
        if (($node instanceof Node\Expr\StaticPropertyFetch || $node instanceof Node\Expr\StaticCall || $node instanceof Node\Expr\ClassConstFetch)
            && $node->class instanceof Node\Name && strcasecmp($node->class->toString(), 'static') === 0
        ) {
            $this->getResult()->addRequirement(RequirementReason::LATE_STATE_BINDING_USING_STATIC, $node->getLine());
        }
    }

    /**
     *
     * non functional needs further investigation or rewrite of Library***Walker to not depend on
     * NameResolver
     * @param \PhpParser\Node $node
     * @codeCoverageIgnore
     */
    private function detectNamespaceSeparator(Node $node)
    {
        if (($node instanceof Node\Expr\StaticPropertyFetch || $node instanceof Node\Expr\StaticCall
                || $node instanceof Node\Expr\ClassConstFetch || $node instanceof Node\Expr\New_)
            && $node->class instanceof Node\Name
            && count($node->class->parts) > 1
            && !in_array($node->class->toString(), $this->importedNames)
        ) {
            $this->getResult()->addRequirement(RequirementReason::NAMESPACE_SEPARATOR, $node->class->getLine());
        }
    }
}
