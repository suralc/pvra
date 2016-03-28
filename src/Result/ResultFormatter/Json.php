<?php
/**
 * Json.php
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
 * @author     suralc <suralc.github@gmail.com>
 * @license    http://opensource.org/licenses/MIT  MIT
 */

namespace Pvra\Result\ResultFormatter;

use Pvra\Result\Collection as ResultCollection;
use Pvra\Result\Exceptions\ResultFileWriterException;

/**
 * Class Json
 *
 * @package Pvra\Result\ResultFormatter
 */
class Json implements ResultFormatter
{
    /**
     * @var int Bitmask of options to be passed to `json_encode`
     */
    private $options = 0;
    /**
     * @var int Depth to be passed to the third parameter of `json_encode`  Only used on php >= 5.5
     */
    private $depth = 512;

    /**
     * Supported option keys:
     *
     * * 'options': Json encoding options
     * * 'depth': Maximum depths
     *
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        $this->options = isset($options['options']) ? $options['options'] : 0;
        $this->depth = isset($options['depth']) ? $options['depth'] : 512;
    }

    /**
     * Generate a json representation of a `Result\Collection`
     *
     * The output is the json representation based on the array returned by
     * `Pvra\Result\Collection::jsonSerialize()`.
     *
     * @param \Pvra\Result\Collection $collection The source collection
     * @return string The returned json string
     * @throws \Pvra\Result\Exceptions\ResultFileWriterException Thrown if json generation failed
     * @see \Pvra\Result\Collection::jsonSerialize() Data source
     */
    public function makePrintable(ResultCollection $collection)
    {
        if (PHP_VERSION_ID >= 50500) {
            $json = json_encode($collection, $this->options, $this->depth);
        } else {
            $json = json_encode($collection, $this->options);
        }
        if (json_last_error() !== 0) {
            $msg = 'Json Encoding failed with error: ' . json_last_error();
            if (PHP_VERSION_ID >= 50500) {
                $msg .= ': ' . json_last_error_msg();
            }
            throw new ResultFileWriterException($msg);
        }

        return $json;
    }
}
