<?php
/**
 * ExtendedEmulativeLexer.php
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
namespace Pvra\Lexer;


use PhpParser\Lexer\Emulative;
use PhpParser\Parser;

class ExtendedEmulativeLexer extends Emulative
{
    /**
     * Create a new Lexer instance with appropriate configuration options
     *
     * Whenever a new lexer instance is created that is meant to be passed to
     * a `Pvra\Analyser` or consumed by a `Pvra\AnalyserAwareInterface` this method should be used.
     *
     * @return ExtendedEmulativeLexer|static
     */
    public static function createDefaultInstance()
    {
        $class = get_called_class();
        return new $class(['usedAttributes' => ['startLine', 'endLine', 'startFilePos', 'startTokenPos']]);
    }

    /**
     * Override to the native getNextToken method
     *
     * This method override ensures that the original value of tokens that would be transformed is stored
     * besides them in the result ast. Depending on the token type various attributes will be added to the token
     * and produced ast. These modifications are required to ensure the correct behaviour of the binary number
     * detection, the detection of booth flavors of the doc syntax, short array syntax and short echo tags.
     *
     * @param null|string $value
     * @param null|array $startAttributes
     * @param null|array $endAttributes
     * @return int Retrieved token id
     * @see https://github.com/nikic/PHP-Parser/issues/26#issuecomment-6150035 Original implementation
     */
    public function getNextToken(&$value = null, &$startAttributes = null, &$endAttributes = null)
    {
        $tokenId = parent::getNextToken($value, $startAttributes, $endAttributes);

        if ($tokenId === Parser::T_LNUMBER || $tokenId === Parser::T_DNUMBER) {
            // could also use $startAttributes, doesn't really matter here
            $endAttributes['originalValue'] = $value;
        } elseif ($tokenId === Parser::T_START_HEREDOC) {
            $startAttributes['isDocSyntax'] = true;
            if (substr($this->code, $startAttributes['startFilePos'] + 3, 1) === '\'') {
                $startAttributes['isNowDoc'] = true;
            } else {
                $startAttributes['isHereDoc'] = true;
            }
        } elseif ($tokenId === Parser::T_ARRAY) {
            $startAttributes['traditionalArray'] = true;
        } elseif ($tokenId === Parser::T_ECHO) {
            if (substr($this->code, $startAttributes['startFilePos'], 3) === '<?=') {
                $startAttributes['isShortEchoTag'] = true;
            }
        }

        return $tokenId;
    }
}
