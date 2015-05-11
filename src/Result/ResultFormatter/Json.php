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
    private $options = 0;
    private $depth = 512;

    /**
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        $this->options = isset($options['options']) ? $options['options'] : 0;
        $this->depth = isset($options['depth']) ? $options['depth'] : 512;
    }

    /**
     * @param \Pvra\Result\Collection $collection
     * @return string
     * @throws \Pvra\Result\Exceptions\ResultFileWriterException
     */
    public function makePrintable(ResultCollection $collection)
    {
        if(PHP_VERSION_ID >= 50500) {
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
