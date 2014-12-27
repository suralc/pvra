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

namespace Pvra\PhpParser\Lexer;


use PhpParser\Lexer\Emulative;
use PhpParser\Parser;

class ExtendedEmulativeLexer extends Emulative
{
    // see https://github.com/nikic/PHP-Parser/issues/26#issuecomment-6150035 as reference
    public function getNextToken(&$value = null, &$startAttributes = null, &$endAttributes = null)
    {
        $tokenId = parent::getNextToken($value, $startAttributes, $endAttributes);

        if ($tokenId == Parser::T_LNUMBER || $tokenId == Parser::T_DNUMBER) {
            // could also use $startAttributes, doesn't really matter here
            $endAttributes['originalValue'] = $value;
        } elseif ($tokenId == Parser::T_ARRAY) {
            $startAttributes['traditionalArray'] = true;
        }

        return $tokenId;
    }
}
