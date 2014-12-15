<?php
/**
 * RequirementAnalysisResult.php
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
namespace Pvra\RequirementAnalysis;


use Pvra\RequirementAnalysis\Result\RequirementReason;
use Pvra\RequirementAnalysis\Result\RequirementReasoning;
use Pvra\RequirementAnalysis\Result\ResultMessageFormatter;

/**
 * Class RequirementAnalysisResult
 *
 * @package Pvra\RequirementAnalysis
 */
class RequirementAnalysisResult implements \IteratorAggregate, \Countable
{
    /**
     * The state of this instance
     *
     * @var bool
     */
    private $isSealed = false;

    /**
     * @var string Filename or hash of input string
     */
    private $analysisTargetId = 'unknown';

    /**
     * @var array|RequirementReasoning[]
     */
    private $requirements = [];

    /**
     * @var string|null
     */
    private $cachedRequiredVersion;
    /**
     * Number of attached reasonings.
     *
     * @var int
     */
    private $count = 0;
    /**
     * @var ResultMessageFormatter
     */
    private $msgFormatter;

    /**
     * @return int
     * @throws \Exception
     */
    public function getRequiredVersionId()
    {
        $version = explode('.', $this->getRequiredVersion());

        $c = count($version);
        if ($c > 3 || $c < 2) {
            throw new \Exception(sprintf('A version id has to be built from two or three segments. "%s" is not valid.',
                $this->getRequiredVersion()));
        }

        $version += [2 => 0];

        return $version[0] * 10000 + $version[1] * 100 + $version[2];
    }

    /**
     * @return \Pvra\RequirementAnalysis\Result\ResultMessageFormatter
     */
    public function getMsgFormatter()
    {
        if ($this->msgFormatter === null) {
            $this->msgFormatter = new ResultMessageFormatter();
        }

        return $this->msgFormatter;
    }

    /**
     * @param \Pvra\RequirementAnalysis\Result\ResultMessageFormatter $formatter
     * @return $this
     */
    public function setMsgFormatter(ResultMessageFormatter $formatter)
    {
        $this->msgFormatter = $formatter;

        return $this;
    }

    /**
     * @return string
     */
    public function getRequiredVersion()
    {
        if ($this->cachedRequiredVersion !== null) {
            return $this->cachedRequiredVersion;
        }

        $keys = array_keys($this->requirements);

        if (!empty($keys)) {
            usort($keys, function ($a, $b) {
                return version_compare($b, $a);
            });

            return $this->cachedRequiredVersion = $keys[0];
        }

        return '5.3.0';
    }

    /**
     * @param string $version
     * @param int $line
     * @param string $msg
     * @param int $reason
     * @param array $data
     */
    public function addArbitraryRequirement(
        $version,
        $line = 0,
        $msg = null,
        $reason = RequirementReason::UNKNOWN,
        array $data = []
    ) {
        if ($this->isSealed()) {
            throw new \RuntimeException('Impossible to write to already sealed result');
        }

        $this->clearInstanceCaches();

        $this->requirements[ $version ][] = new RequirementReasoning($reason, $line, $this, $version, $msg, $data);
        $this->count++;
    }

    /**
     * @param int $reason
     * @param int $line
     * @param string $msg
     * @param array $data
     */
    public function addRequirement($reason, $line = 0, $msg = null, array $data = [])
    {
        if ($this->isSealed()) {
            throw new \RuntimeException('Impossible to write to already sealed result');
        }

        $version = RequirementReason::getRequiredVersionForReason($reason);

        if ($version === false) {
            throw new \LogicException(sprintf('%s::%s requires a reason a version can be associated to. Use %s::addArbitraryRequirement() to add any version with any reasoning to the result.',
                __CLASS__, __METHOD__, __CLASS__));
        }

        $this->clearInstanceCaches();

        $this->requirements[ $version ][] = new RequirementReasoning($reason, $line, $this, $version, $msg, $data);
        $this->count++;
    }

    /**
     * @return bool
     */
    public function isSealed()
    {
        return $this->isSealed;
    }


    /**
     * @return array
     */
    public function getRequirements()
    {
        return $this->requirements;

    }

    /**
     * @param string $version
     * @return array
     */
    public function getRequirementInfo($version)
    {
        if (isset($this->requirements[ $version ])) {
            return $this->requirements[ $version ];
        }

        return [];
    }

    /**
     * @return string
     */
    public function getAnalysisTargetId()
    {
        return $this->analysisTargetId;
    }

    /**
     * @param string $analysisTargetId
     * @return $this
     */
    public function setAnalysisTargetId($analysisTargetId)
    {
        if ($this->isSealed()) {
            throw new \RuntimeException('You cannot modify an already sealed result.');
        }

        $this->analysisTargetId = $analysisTargetId;

        return $this;
    }

    /**
     *
     */
    public function seal()
    {
        $this->isSealed = true;
    }

    /**
     * @inheritdoc
     */
    public function getIterator()
    {
        $it = new \ArrayIterator();
        foreach ($this->getRequirements() as $requirementVersion => $values) {
            foreach ($values as $value) {
                $it->append($value);
            }
        }

        return $it;
    }

    /**
     * @return int
     */
    public function count()
    {
        return $this->count;
    }

    /**
     *
     */
    private function clearInstanceCaches()
    {
        $this->cachedRequiredVersion = null;
    }
}