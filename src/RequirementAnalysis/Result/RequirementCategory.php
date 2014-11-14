<?php

namespace Pvra\RequirementAnalysis\Result;


final class RequirementCategory
{
    const UNKNOWN = 0,
        TRAIT_DEFINITION = 1,
        TRAIT_USE = 2,
        FUNCTION_IMPORT_USE = 3,
        CONSTANT_IMPORT_USE = 4,
        GENERATOR_DEFINITION = 5,
        ARRAY_FUNCTION_DEREFERENCING = 6,
        TYPEHINT_CALLABLE = 7,
        FUNCTION_VARIADIC = 8,
        INSTANT_CLASS_MEMBER_ACCESS = 9,
        THIS_IN_CLOSURE = 10,
        TRY_CATCH_FINALLY = 11,
        LIST_IN_FOREACH = 12,
        EXPR_IN_EMPTY = 13,
        ARRAY_STRING_DEREFERENCING = 14,
        CLASS_NAME_RESOLUTION = 15,
        CONSTANT_SCALAR_EXPRESSION = 16,
        ARGUMENT_UNPACKING = 17,
        POW_OPERATOR = 18,
        TRAIT_MAGIC_CONST = 19;

    private function __construct()
    {
    }
}
