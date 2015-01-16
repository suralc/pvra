<?php
/**
 * StringAnalyser.php
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
namespace Pvra;

/**
 * Class StringAnalyser
 *
 * @package Pvra
 */
class StringAnalyser extends Analyser
{
    /**
     * The string to analyse
     *
     * @var string
     */
    private $string;

    /**
     * StringAnalyser constructor
     *
     * @param string $string The code to analyse
     * @param bool $registerNameResolver Inherited from the base class `Analyser`
     * @see Analyser::__construct() Base Constructor
     */
    public function __construct($string, $registerNameResolver = true)
    {
        $this->string = $string;
        parent::__construct($registerNameResolver);
    }

    /**
     * @inheritdoc
     */
    protected function parse()
    {
        return $this->getParser()->parse($this->string);
    }

    /**
     * @inheritdoc
     */
    protected function createAnalysisTargetId()
    {
        return md5($this->string);
    }
}
