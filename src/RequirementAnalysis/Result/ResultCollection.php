<?php
/**
 * ResultCollection.php
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


use Pvra\RequirementAnalysis\RequirementAnalysisResult;
use Traversable;

/**
 * Class ResultCollection
 *
 * @package Pvra\RequirementAnalysis\Result
 */
class ResultCollection implements \Countable, \IteratorAggregate, \JsonSerializable
{
    /**
     * @var \Pvra\RequirementAnalysis\RequirementAnalysisResult[]
     */
    private $results = [];
    /**
     * @var string
     */
    private $highestDemand;

    /**
     * @param \Pvra\RequirementAnalysis\RequirementAnalysisResult $result
     * @param bool $ignoreIfExists
     * @return $this
     */
    public function add(RequirementAnalysisResult $result, $ignoreIfExists = true)
    {
        if ($ignoreIfExists === true && $this->has($result)) {
            return $this;
        }

        if ($this->highestDemand === null
            || (isset($this->results[ $this->highestDemand ])
                && $this->results[ $this->highestDemand ]->getRequiredVersionId()
                < $result->getRequiredVersionId())
        ) {
            $this->highestDemand = $result->getAnalysisTargetId();
        }

        $this->results[ $result->getAnalysisTargetId() ] = $result;

        return $this;
    }

    /**
     * @param $result
     * @return bool
     */
    public function has($result)
    {
        if (is_string($result)) {
            return isset($this->results[ $result ]);
        } elseif ($result instanceof RequirementAnalysisResult) {
            return isset($this->results[ $result->getAnalysisTargetId() ]);
        } else {
            throw new \InvalidArgumentException(sprintf('"%s" is not a valid keytype in a ResultCollection',
                gettype($result)));
        }
    }

    /**
     * @return null|\Pvra\RequirementAnalysis\RequirementAnalysisResult
     */
    public function getHighestDemandingResult()
    {
        if (isset($this->results[ $this->highestDemand ])) {
            return $this->results[ $this->highestDemand ];
        } else {
            return null;
        }
    }

    /**
     * Retrieve an external iterator
     *
     * @link http://php.net/manual/en/iteratoraggregate.getiterator.php
     * @return Traversable|RequirementAnalysisResult[]
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->results);
    }

    /**
     * Count attached results
     *
     * @link http://php.net/manual/en/countable.count.php
     * @return int The custom count as an integer.
     * The return value is cast to an integer.
     */
    public function count()
    {
        return count($this->results);
    }

    /**
     * Specify data which should be serialized to JSON
     *
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by json_encode,
     * which is a value of any type other than a resource.
     */
    public function jsonSerialize()
    {
        $results = [];
        foreach ($this->getIterator() as $result) {
            $reasonings = [];
            foreach ($result->getIterator() as $reason) {
                $reasonings[] = $reason->toArray();
            }
            $results[ $result->getAnalysisTargetId() ] = $reasonings;
        }

        return $results;
    }
}
