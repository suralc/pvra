<?php
/**
 * Reason.php
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
 * @author     suralc <suralc.github@gmail.com>
 * @license    http://opensource.org/licenses/MIT  MIT
 */
namespace Pvra\Result;

/**
 * Class Reason
 *
 * @package Pvra\Result
 */
abstract class Reason
{
    // These integers are arbitrary. They may appear to follow some system, but they do not. Always use the constants.
    const UNKNOWN = 0,
        LIB_CLASS_ADDITION = 1,
        LIB_CLASS_REMOVAL = 3,
        LIB_CLASS_DEPRECATION = 6,
        LIB_FUNCTION_ADDITION = 2,
        LIB_FUNCTION_REMOVAL = 4,
        LIB_FUNCTION_DEPRECATION = 5,
        LIB_CONSTANT_ADDITION = 7,
        LIB_CONSTANT_DEPRECATION = 8,
        LIB_CONSTANT_REMOVAL = 9,
        // 5.3
        GOTO_KEYWORD = 70,
        JUMP_LABEL = 71,
        NAMESPACE_DECLERATION = 72,
        NAMESPACE_MAGIC_CONSTANT = 73,
        NAMESPACE_IMPORT = 74,
        NOWDOC_LITERAL = 75,
        CALLSTATIC_MAGIC_METHOD = 76,
        INVOKE_MAGIC_METHOD = 77,
        CONST_KEYWORD_OUTSIDE_CLASS = 78,
        CONST_KEYWORD_DOC_SYNTAX = 79,
        SHORT_TERNARY = 80,
        CLOSURE_DECLARATION = 81,
        DYNAMIC_ACCESS_TO_STATIC = 82,
        LATE_STATE_BINDING_USING_STATIC = 83,
        NAMESPACE_SEPARATOR = 84,
        DIR_MAGIC_CONSTANT = 85,
        NEW_ASSIGN_BY_REF_DEP = 86,
        // 5.4
        TRAIT_DEFINITION = 10,
        TRAIT_USE = 11,
        TRAIT_MAGIC_CONST = 12,
        ARRAY_FUNCTION_DEREFERENCING = 13,
        THIS_IN_CLOSURE = 14,
        TYPEHINT_CALLABLE = 15,
        INSTANT_CLASS_MEMBER_ACCESS = 16,
        BINARY_NUMBER_DECLARATION = 17,
        SHORT_ARRAY_DECLARATION = 18,
        STATIC_CALL_BY_EXPRESSION = 19,
        SHORT_ECHO_TAG = 20,
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
        CONSTANT_IMPORT_USE = 55,
        // 7.0
        RESERVED_CLASS_NAME = 100,
        SOFT_RESERVED_NAME = 101,
        PHP4_CONSTRUCTOR = 103,
        COALESCE_OPERATOR = 104,
        SPACESHIP_OPERATOR = 105,
        RETURN_TYPE = 106,
        YIELD_FROM = 107,
        ANON_CLASS = 108,
        NEW_ASSIGN_BY_REF_REM = 109;

    /**
     * @var array|null
     */
    private static $reasonToVersion;
    /**
     * @var array|null
     */
    private static $constantsCache;

    /**
     * Get version information from a reason.
     *
     * This static method may be used to get version information of a reason constant defined in Reason.
     * If no matching version for a constant can be found an InvalidArgumentException will be thrown.
     *
     * This method may return a falseable string or bool(false). Use !== false to check for a valid returned version.
     * If false is returned refer to the corresponding method on the class that returned the constant.
     *
     * @param int $reason One of the constants defined in RequirementReason
     * @return string|false The required version or bool(false)
     */
    public static function getVersionFromReason($reason)
    {
        if (self::$reasonToVersion === null) {
            self::$reasonToVersion = static::getReasonToVersionBaseValues();
        }

        if (isset(self::$reasonToVersion[ $reason ])) {
            return self::$reasonToVersion[ $reason ];
        } elseif ($reason > self::UNKNOWN && $reason < self::TRAIT_DEFINITION) {
            return false;
        } else {
            throw new \InvalidArgumentException(sprintf('There is no required version defined for this reason(id: "%s", name: "%s").',
                $reason, static::getReasonNameFromValue($reason)));
        }
    }

    /**
     * Get the constant name from constant value
     *
     * @param int $value
     * @return string The name of the constant or 'UNKNOWN'
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

    /**
     * Get a list of defined constants and their values.
     *
     * @return array
     */
    public static function getReasonNames()
    {
        if (self::$constantsCache !== null) {
            return self::$constantsCache;
        }

        $constants = (new \ReflectionClass(get_called_class()))
            ->getConstants();

        foreach ($constants as $name => $value) {
            self::$constantsCache[ $name ] = $value;
        }

        return self::$constantsCache;
    }

    /**
     * Clears the cached constant lists.
     *
     * May be useful to save memory or to force regeneration of the list in tests.
     */
    public static function clear()
    {
        self::$reasonToVersion = null;
        self::$constantsCache = null;
    }

    /**
     * Map reasons to versions
     *
     * This method returns an array having the constants defined by this class as keys and their required
     * php versions as values.
     *
     * @return array
     */
    protected static function getReasonToVersionBaseValues()
    {
        return [
            self::UNKNOWN => '7.0.0',
            // 5.4
            self::GOTO_KEYWORD => '5.3.0',
            self::JUMP_LABEL => '5.3.0',
            self::NAMESPACE_DECLERATION => '5.3.0',
            self::NAMESPACE_MAGIC_CONSTANT => '5.3.0',
            self::NAMESPACE_IMPORT => '5.3.0',
            self::NAMESPACE_SEPARATOR => '5.3.0',
            self::NOWDOC_LITERAL => '5.3.0',
            self::CALLSTATIC_MAGIC_METHOD => '5.3.0',
            self::INVOKE_MAGIC_METHOD => '5.3.0',
            self::CONST_KEYWORD_OUTSIDE_CLASS => '5.3.0',
            self::CONST_KEYWORD_DOC_SYNTAX => '5.3.0',
            self::SHORT_TERNARY => '5.3.0',
            self::CLOSURE_DECLARATION => '5.3.0',
            self::DYNAMIC_ACCESS_TO_STATIC => '5.3.0',
            self::LATE_STATE_BINDING_USING_STATIC => '5.3.0',
            self::DIR_MAGIC_CONSTANT => '5.3.0',
            self::NEW_ASSIGN_BY_REF_DEP => '5.3.0',
            // 5.4
            self::TRAIT_DEFINITION => '5.4.0',
            self::TRAIT_USE => '5.4.0',
            self::TRAIT_MAGIC_CONST => '5.4.0',
            self::ARRAY_FUNCTION_DEREFERENCING => '5.4.0',
            self::THIS_IN_CLOSURE => '5.4.0',
            self::TYPEHINT_CALLABLE => '5.4.0',
            self::INSTANT_CLASS_MEMBER_ACCESS => '5.4.0',
            self::BINARY_NUMBER_DECLARATION => '5.4.0',
            self::SHORT_ARRAY_DECLARATION => '5.4.0',
            self::STATIC_CALL_BY_EXPRESSION => '5.4.0',
            self::SHORT_ECHO_TAG => '5.4.0',
            // 5.5
            self::GENERATOR_DEFINITION => '5.5.0',
            self::TRY_CATCH_FINALLY => '5.5.0',
            self::LIST_IN_FOREACH => '5.5.0',
            self::EXPR_IN_EMPTY => '5.5.0',
            self::ARRAY_OR_STRING_DEREFERENCING => '5.5.0',
            self::CLASS_NAME_RESOLUTION => '5.5.0',
            // 5.6
            self::VARIADIC_ARGUMENT => '5.6.0',
            self::ARGUMENT_UNPACKING => '5.6.0',
            self::CONSTANT_SCALAR_EXPRESSION => '5.6.0',
            self::POW_OPERATOR => '5.6.0',
            self::FUNCTION_IMPORT_USE => '5.6.0',
            self::CONSTANT_IMPORT_USE => '5.6.0',
            // 7.0
            self::RESERVED_CLASS_NAME => '7.0.0',
            self::SOFT_RESERVED_NAME => '7.0.0',
            self::PHP4_CONSTRUCTOR => '7.0.0',
            self::COALESCE_OPERATOR => '7.0.0',
            self::SPACESHIP_OPERATOR => '7.0.0',
            self::RETURN_TYPE => '7.0.0',
            self::YIELD_FROM => '7.0.0',
            self::ANON_CLASS => '7.0.0',
            self::NEW_ASSIGN_BY_REF_REM => '7.0.0',
        ];
    }

    /**
     * @codeCoverageIgnore
     */
    private function __construct()
    {
    }
}
