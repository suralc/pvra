<?php
/**
 * CollectionWriter.php
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

namespace Pvra\Result;

use Pvra\Result\Collection as ResultCollection;
use Pvra\Result\Exceptions\ResultFileWriterException;
use Pvra\Result\ResultFormatter\ResultFormatter;

/**
 * Class CollectionWriter
 *
 * @package Pvra\Result
 */
class CollectionWriter
{
    /**
     * @var \Pvra\Result\Collection
     */
    private $result;

    /**
     * @param \Pvra\Result\Collection $result
     */
    public function __construct(ResultCollection $result)
    {
        $this->result = $result;
    }

    /**
     * @param $fileName
     * @param \Pvra\Result\ResultFormatter\ResultFormatter $formatter
     * @param bool $overrideExistingFile
     * @throws \Pvra\Result\Exceptions\ResultFileWriterException
     */
    public function write($fileName, ResultFormatter $formatter, $overrideExistingFile = false)
    {
        if ($overrideExistingFile !== true && file_exists($fileName)) {
            throw new ResultFileWriterException($fileName . ' already exists. Cannot override an already existing file!');
        }
        $formatted = $formatter->makePrintable($this->result);
        file_put_contents($fileName, $formatted);
    }

    /**
     * @param $stream
     * @param \Pvra\Result\ResultFormatter\ResultFormatter $formatter
     */
    public function writeToStream($stream, ResultFormatter $formatter)
    {
        if (!is_resource($stream)) {
            throw new \InvalidArgumentException(sprintf('Parameter 1 of method CollectionWriter::writeToStream has to be a resource. %s given',
                gettype($stream)));
        }
        $formatted = $formatter->makePrintable($this->result);
        fwrite($stream, $formatted);
    }
}
