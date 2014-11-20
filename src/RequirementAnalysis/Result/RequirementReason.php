<?php

namespace Pvra\RequirementAnalysis\Result;


abstract class RequirementReason
{
    const UNKNOWN = 0x0,
        CLASS_PRESENCE_CHANGE = 1,
        FUNCTION_PRESENCE_CHANGE = 2,
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
    private static $constantsCache;

    /**
     * @param int $reason One of the constants defined in RequirementReason
     * @return string
     */
    public static function getRequiredVersionForReason($reason)
    {
        if (static::$reasonToRequirement === null) {
            static::$reasonToRequirement = static::getReasonToRequirementBaseValues();
        }

        if (isset(static::$reasonToRequirement[ $reason ])) {
            return static::$reasonToRequirement[ $reason ];
        } elseif ($reason > static::UNKNOWN && $reason < static::TRAIT_DEFINITION) {
            return false;
        } else {
            throw new \InvalidArgumentException(sprintf('There is no required version defined for this reason(id: "%s").',
                $reason));
        }
    }

    /**
     * @param int $value
     * @return string
     */
    public static function getReasonNameFromValue($value)
    {
        $names = static::getReasonNames();
        $names = array_flip($names);

        if (isset($names[ $value ])) {
            return $names[ $value ];
        }

        return 'UNKNOWN';
    }

    public static function getReasonNames()
    {
        if (self::$constantsCache !== null) {
            return self::$constantsCache;
        }

        $class = get_called_class();
        $constants = (new \ReflectionClass($class))->getConstants();

        foreach ($constants as $name => $value) {
            self::$constantsCache[ $name ] = $value;
        }

        return self::$constantsCache;
    }


    public static function clear()
    {
        static::$reasonToRequirement = null;
        static::$constantsCache = null;
    }

    protected static function getReasonToRequirementBaseValues()
    {
        return [
            static::UNKNOWN => '7.0.0',
            // 5.4
            static::TRAIT_DEFINITION => '5.4.0',
            static::TRAIT_USE => '5.4.0',
            static::TRAIT_MAGIC_CONST => '5.4.0',
            static::ARRAY_FUNCTION_DEREFERENCING => '5.4.0',
            static::THIS_IN_CLOSURE => '5.4.0',
            static::TYPEHINT_CALLABLE => '5.4.0',
            static::INSTANT_CLASS_MEMBER_ACCESS => '5.4.0',
            // 5.5
            static::GENERATOR_DEFINITION => '5.5.0',
            static::TRY_CATCH_FINALLY => '5.5.0',
            static::LIST_IN_FOREACH => '5.5.0',
            static::EXPR_IN_EMPTY => '5.5.0',
            static::ARRAY_OR_STRING_DEREFERENCING => '5.5.0',
            static::CLASS_NAME_RESOLUTION => '5.5.0',
            // 5.6
            static::VARIADIC_ARGUMENT => '5.6.0',
            static::ARGUMENT_UNPACKING => '5.6.0',
            static::CONSTANT_SCALAR_EXPRESSION => '5.6.0',
            static::POW_OPERATOR => '5.6.0',
            static::FUNCTION_IMPORT_USE => '5.6.0',
            static::CONSTANT_IMPORT_USE => '5.6.0',
        ];
    }

    private function __construct()
    {
    }
}