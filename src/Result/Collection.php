<?php
/**
 * Collection.php
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


use Pvra\AnalysisResult;
use Traversable;

/**
 * Class Collection
 *
 * @package Pvra\Result
 */
class Collection implements \Countable, \IteratorAggregate, \JsonSerializable
{
    /**
     * Result instances that are part of this collection
     *
     * @var \Pvra\AnalysisResult[]
     */
    private $results = [];
    /**
     * The targetId of the currently highest demanding result
     *
     * @var string
     */
    private $highestDemand;

    /**
     * Add a result to this collection
     *
     * @param \Pvra\AnalysisResult $result The result to be added
     * @param bool $ignoreIfExists Do not add+override an already existing result with the same targetId.
     * @return $this
     */
    public function add(AnalysisResult $result, $ignoreIfExists = true)
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
     * Remove a result from the collection
     *
     * Removes a result, which is identified by its analysis target id  from
     * the current collection.
     *
     * @param string|AnalysisResult $result
     * @return true
     */
    public function remove($result)
    {
        if ($result instanceof AnalysisResult) {
            $id = $result->getAnalysisTargetId();
        } elseif (is_string($result)) {
            $id = $result;
            if (isset($this->results[ $id ])) {
                $result = $this->results[ $id ];
            } else {
                return true;
            }
        } else {
            throw new \InvalidArgumentException('The result argument has to be an instance of AnalysisResult or a string.');
        }

        $needRecalc = false;
        if ($this->highestDemand !== null && $result->getAnalysisTargetId() === $this->getHighestDemandingResult()->getAnalysisTargetId()) {
            $needRecalc = true;
        }

        unset($this->results[ $id ]);

        if ($needRecalc) {
            $this->recalculateHighestDemandingResult();
        }

        return true;
    }

    /**
     * Recalculates the highest demanding result of this collection
     *
     * This can be expensive. Only do this if really necessary.
     */
    private function recalculateHighestDemandingResult()
    {
        $highestVersionId = 0;
        $highestResult = null;
        foreach ($this->results as $result) {
            if ($result->getRequiredVersionId() > $highestVersionId) {
                $highestVersionId = $result->getRequiredVersionId();
                $highestResult = $result;
            }
        }

        if ($highestResult !== null) {
            $this->highestDemand = $highestResult->getAnalysisTargetId();
        } else {
            $this->highestDemand = null;
        }
    }

    /**
     * Checks whether a given result is part of this collection
     *
     * @param string|\Pvra\AnalysisResult $result Analysis target Id or instance of Analysis Result
     * @return bool
     */
    public function has($result)
    {
        if (is_string($result)) {
            return isset($this->results[ $result ]);
        } elseif ($result instanceof AnalysisResult) {
            return isset($this->results[ $result->getAnalysisTargetId() ]);
        }

        return false;
    }

    /**
     * Get the currently highest demanding result
     *
     * @return null|\Pvra\AnalysisResult
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
     * @return Traversable|AnalysisResult[]
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
