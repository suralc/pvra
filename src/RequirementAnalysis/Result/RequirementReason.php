<?php
/**
 * RequirementReason.php
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
namespace Pvra\RequirementAnalysis\Result;

/**
 * Class RequirementReason
 *
 * @package Pvra\RequirementAnalysis\Result
 */
abstract class RequirementReason
{
    // These integers are arbitrary. They may appear to follow some system, but be aware
    // they do not. Always use the constants.
    const UNKNOWN = 0x0,
        CLASS_PRESENCE_CHANGE = 1,
        FUNCTION_PRESENCE_CHANGE = 2,
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
        CONSTANT_IMPORT_USE = 55;

    /**
     * @var array
     */
    private static $reasonToRequirement;
    /**
     * @var array
     */
    private static $constantsCache;

    /**
     * Get the required version for a reason.
     *
     * This static method may be used to get the required version of a reason constant defined in RequirementReason.
     * If no matching version for a constant can be found an InvalidArgumentException will be thrown.
     *
     * This method may return a falseable string or bool(false). Use !== false to check for a valid returned version.
     * If false is returned refer to the corresponding method on the class that returned the constant.
     *
     * @param int $reason One of the constants defined in RequirementReason
     * @return string|bool The required version or bool(false)
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
        static::$reasonToRequirement = null;
        static::$constantsCache = null;
    }

    /**
     * Map reasons to versions
     *
     * This method returns an array having the constants defined by this class as keys and their required
     * php versions as values.
     *
     * @return array
     */
    protected static function getReasonToRequirementBaseValues()
    {
        return [
            static::UNKNOWN => '7.0.0',
            // 5.4
            static::GOTO_KEYWORD => '5.3.0',
            static::JUMP_LABEL => '5.3.0',
            static::NAMESPACE_DECLERATION => '5.3.0',
            static::NAMESPACE_MAGIC_CONSTANT => '5.3.0',
            static::NAMESPACE_IMPORT => '5.3.0',
            static::NAMESPACE_SEPARATOR => '5.3.0',
            static::NOWDOC_LITERAL => '5.3.0',
            static::CALLSTATIC_MAGIC_METHOD => '5.3.0',
            static::INVOKE_MAGIC_METHOD => '5.3.0',
            static::CONST_KEYWORD_OUTSIDE_CLASS => '5.3.0',
            static::CONST_KEYWORD_DOC_SYNTAX => '5.3.0',
            static::SHORT_TERNARY => '5.3.0',
            static::CLOSURE_DECLARATION => '5.3.0',
            static::DYNAMIC_ACCESS_TO_STATIC => '5.3.0',
            static::LATE_STATE_BINDING_USING_STATIC => '5.3.0',
            // 5.4
            static::TRAIT_DEFINITION => '5.4.0',
            static::TRAIT_USE => '5.4.0',
            static::TRAIT_MAGIC_CONST => '5.4.0',
            static::ARRAY_FUNCTION_DEREFERENCING => '5.4.0',
            static::THIS_IN_CLOSURE => '5.4.0',
            static::TYPEHINT_CALLABLE => '5.4.0',
            static::INSTANT_CLASS_MEMBER_ACCESS => '5.4.0',
            static::BINARY_NUMBER_DECLARATION => '5.4.0',
            static::SHORT_ARRAY_DECLARATION => '5.4.0',
            static::STATIC_CALL_BY_EXPRESSION => '5.4.0',
            static::SHORT_ECHO_TAG => '5.4.0',
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

    /**
     * @codeCoverageIgnore
     */
    private function __construct()
    {
    }
}
