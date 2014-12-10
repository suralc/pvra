<?php

namespace Pvra\data;

use Pvra\RequirementAnalysis\Result\RequirementReason as R;

return [
    R::UNKNOWN => 0x0,
    R::CLASS_PRESENCE_CHANGE => 'The ":className:" class was introduced in PHP :version:',
    R::FUNCTION_PRESENCE_CHANGE => 'The ":functionName:" function was introduced in PHP :version:',
    // 5.4
    R::TRAIT_DEFINITION => 'Usage of the trait keyword requires PHP :version:',
    R::TRAIT_USE => 'Usage of trait imports requires PHP :version:',
    R::TRAIT_MAGIC_CONST => 'Usage of the trait magic constant requires PHP :version:',
    R::ARRAY_FUNCTION_DEREFERENCING => 'Function dereferencing requires PHP :version:',
    R::THIS_IN_CLOSURE => 'Usage of $this in closures requires PHP :version:',
    R::TYPEHINT_CALLABLE => 'The callable typehint requires PHP :version:',
    R::INSTANT_CLASS_MEMBER_ACCESS => 'Instant class member access requires PHP :version',
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
