<?php
/**
 * ResultFormatter.php
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

/**
 * Interface ResultFormatter
 *
 * @package Pvra\Result\ResultFormatter
 */
interface ResultFormatter
{
    public function __construct(array $options = []);

    /**
     * @param \Pvra\Result\Collection $collection
     * @return string
     */
    public function makePrintable(ResultCollection $collection);
}
