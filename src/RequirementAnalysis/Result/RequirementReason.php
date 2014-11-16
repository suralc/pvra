<?php

namespace Pvra\RequirementAnalysis\Result;


abstract class RequirementReason
{
    const UNKNOWN = 0x0,
        // 5.4
        TRAIT_DEFINITION = 10,
        TRAIT_USE = 11,
        TRAIT_MAGIC_CONST = 12,
        ARRAY_FUNCTION_DEREFERENCING = 13,
        THIS_IN_CLOSURE = 14,
        TYPEHINT_CALLABLE = 15,
        INSTANT_CLASS_MEMBER_ACCESS = 16,
        // 5.5
        GENERATOR_DEFINITION = 30,
        TRY_CATCH_FINALLY = 31,
        LIST_IN_FOREACH = 32,
        EXPR_IN_EMPTY = 33,
        ARRAY_OR_STRING_DEREFERENCING = 34,
        CLASS_NAME_RESOLUTION = 35,
        // 5.6
        VARIADIC_ARGUMENT = 50,
        ARGUMENT_UNPACKING = 51,
        CONSTANT_SCALAR_EXPRESSION = 52,
        POW_OPERATOR = 53,
        FUNCTION_IMPORT_USE = 54,
        CONSTANT_IMPORT_USE = 55;

    private static $reasonToRequirement;

    public static function getRequiredVersionForReason($reason, $default = '7.0')
    {
        if (static::$reasonToRequirement === null) {
            static::$reasonToRequirement = static::getReasonToRequirementBaseValues();
        }

        return isset(static::$reasonToRequirement[ $reason ]) ? static::$reasonToRequirement[ $reason ] : $default;
    }

    public static function clear()
    {
        static::$reasonToRequirement = null;
    }

    protected static function getReasonToRequirementBaseValues()
    {
        return [
            static::UNKNOWN => '5.3',
            // 5.4
            static::TRAIT_DEFINITION => '5.4',
            static::TRAIT_USE => '5.4',
            static::TRAIT_MAGIC_CONST => '5.4',
            static::ARRAY_FUNCTION_DEREFERENCING => '5.4',
            static::THIS_IN_CLOSURE => '5.4',
            static::TYPEHINT_CALLABLE => '5.4',
            static::INSTANT_CLASS_MEMBER_ACCESS => '5.4',
            // 5.5
            static::GENERATOR_DEFINITION => '5.5',
            static::TRY_CATCH_FINALLY => '5.5',
            static::LIST_IN_FOREACH => '5.5',
            static::EXPR_IN_EMPTY => '5.5',
            static::ARRAY_OR_STRING_DEREFERENCING => '5.5',
            static::CLASS_NAME_RESOLUTION => '5.5',
            // 5.6
            static::VARIADIC_ARGUMENT => '5.6',
            static::ARGUMENT_UNPACKING => '5.6',
            static::CONSTANT_SCALAR_EXPRESSION => '5.6',
            static::POW_OPERATOR => '5.6',
            static::FUNCTION_IMPORT_USE => '5.6',
            static::CONSTANT_IMPORT_USE => '5.6',
        ];
    }

    private function __construct()
    {
    }
}
