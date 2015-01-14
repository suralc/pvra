<?php
/**
 * default_messages.php
 *
 * MIT LICENSE
 *
 * LICENSE: This source file is subject to the MIT license.
 * A copy of the licenses text was distributed alongside this
 * file (usually the repository or package root). The text can also
 * be obtained through one of the following sources:
 * * http://opensource.org/licenses/MIT
 * * https://github.com/suralc/pvra/blob/master/LICENSE
 *
 * @author     suralc <thesurwaveing@gmail.com>
 * @license    http://opensource.org/licenses/MIT  MIT
 */
namespace Pvra\data;

use Pvra\RequirementAnalysis\Result\RequirementReason as R;

return [
    R::UNKNOWN => 0x0,
    R::CLASS_PRESENCE_CHANGE => 'The ":className:" class was introduced in PHP :version:',
    R::FUNCTION_PRESENCE_CHANGE => 'The ":functionName:" function was introduced in PHP :version:',
    // 5.3
    R::GOTO_KEYWORD => 'The goto keyword (targeting :name:) was introduced in PHP :version:',
    R::JUMP_LABEL => 'The :name: Jump label is only usable with PHP :version: or above',
    R::NAMESPACE_DECLERATION => 'The namespace keyword was introduced in PHP :version:',
    R::NAMESPACE_IMPORT => 'The ability to import namespaces was introduced in PHP :version:',
    R::NAMESPACE_MAGIC_CONSTANT => 'The __NAMESPACE__ constant was introduced in PHP :version:',
    R::NAMESPACE_SEPARATOR => 'The namespace separator "\" is only available in PHP :version: or later',
    R::NOWDOC_LITERAL => 'The NOWDOC syntax was introduced in PHP :version:',
    R::CALLSTATIC_MAGIC_METHOD => 'The __callStatic magic method would not be called before PHP :version:',
    R::INVOKE_MAGIC_METHOD => 'The __invoke magic method would not be called before PHP :version:',
    R::CONST_KEYWORD_OUTSIDE_CLASS => 'The const keyword could not be used outside classes before PHP :version:',
    R::CONST_KEYWORD_DOC_SYNTAX => 'A constant value could not be defined using the doc syntax before PHP :version:',
    R::SHORT_TERNARY => 'The short ternary syntax was not available before PHP :version:',
    R::CLOSURE_DECLARATION => 'It was not possible before PHP :version: to declare closures or anonymous functions',
    R::DYNAMIC_ACCESS_TO_STATIC => 'Static methods and properties could not be accessed dynamically before PHP :version:',
    R::LATE_STATE_BINDING_USING_STATIC => 'Late state binding was not available before PHP :version:',
    // 5.4
    R::TRAIT_DEFINITION => 'Usage of the trait keyword requires PHP :version:',
    R::TRAIT_USE => 'Usage of trait imports requires PHP :version:',
    R::TRAIT_MAGIC_CONST => 'Usage of the trait magic constant requires PHP :version:',
    R::ARRAY_FUNCTION_DEREFERENCING => 'Function dereferencing requires PHP :version:',
    R::THIS_IN_CLOSURE => 'Usage of $this in closures requires PHP :version:',
    R::TYPEHINT_CALLABLE => 'The callable typehint requires PHP :version:',
    R::INSTANT_CLASS_MEMBER_ACCESS => 'Instant class member access requires PHP :version:',
    R::BINARY_NUMBER_DECLARATION => 'Binary representation of numbers requires PHP :version:',
    R::SHORT_ARRAY_DECLARATION => 'Usage of the short array syntax requires PHP :version:',
    R::STATIC_CALL_BY_EXPRESSION => 'Class::{expr}() syntax requires PHP :version:',
    R::SHORT_ECHO_TAG => 'It is not reliable to depend on the short echo tag (<?=) before PHP :version:',
    // 5.5
    R::GENERATOR_DEFINITION => 'Usage of generators requires PHP :version:',
    R::TRY_CATCH_FINALLY => 'Usage of the finally keyword requires PHP :version:',
    R::LIST_IN_FOREACH => 'Usage of list in foreach ValueVar statement requires PHP :version:',
    R::EXPR_IN_EMPTY => 'Usage of arbitrary expressions in empty statement requires PHP :version:',
    R::ARRAY_OR_STRING_DEREFERENCING => 'Array and string literal dereferencing requires PHP :version',
    R::CLASS_NAME_RESOLUTION => 'Class name resolution via ::class requires PHP :version:',
    // 5.6
    R::VARIADIC_ARGUMENT => 'Variadic arguments require PHP :version:',
    R::ARGUMENT_UNPACKING => 'Argument unpacking requires PHP :version:',
    R::CONSTANT_SCALAR_EXPRESSION => 'Constant scalar expressions require PHP :version:',
    R::POW_OPERATOR => 'The "pow" operator requires PHP :version:',
    R::CONSTANT_IMPORT_USE => 'Constant import via use requires PHP :version:',
];
