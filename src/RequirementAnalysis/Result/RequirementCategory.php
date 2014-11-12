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
        THIS_IN_CLOSURE = 10;

    private function __construct()
    {
    }
}
