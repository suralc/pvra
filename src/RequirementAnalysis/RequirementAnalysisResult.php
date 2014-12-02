<?php

namespace Pvra\RequirementAnalysis;


use Pvra\RequirementAnalysis\Result\RequirementReason;

class RequirementAnalysisResult implements \IteratorAggregate, \Countable
{
    /**
     * @var bool
     */
    private $isSealed = false;

    /**
     * @var string Filename or hash of input string
     */
    private $analysisTargetId = 'unknown';

    /**
     * @var array
     */
    private $requirements = [];

    /**
     * @var string|null
     */
    private $cachedRequiredVersion;
    /**
     * @var int
     */
    private $count = 0;

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
     */
    public function addArbitraryRequirement($version, $line = 0, $msg = null, $reason = RequirementReason::UNKNOWN)
    {
        if ($this->isSealed()) {
            throw new \RuntimeException('Impossible to write to already sealed result');
        }

        $this->clearInstanceCaches();

        $this->requirements[ $version ][] = [
            'line' => $line,
            'msg' => $msg,
            'reason' => $reason,
        ];
        $this->count++;
    }

    /**
     * @param int $reason
     * @param int $line
     * @param string $msg
     */
    public function addRequirement($reason, $line = 0, $msg = null)
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

        $this->requirements[ $version ][] = [
            'line' => $line,
            'msg' => $msg,
            'reason' => $reason,
        ];
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
     */
    public function setAnalysisTargetId($analysisTargetId)
    {
        $this->analysisTargetId = $analysisTargetId;
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
        $ar = new \ArrayIterator();
        foreach ($this->getRequirements() as $version => $reqList) {
            foreach ($reqList as $requirement) {
                $requirement += ['version' => $version];
                $ar->append($requirement);
            }
        }

        return $ar;
    }

    public function count()
    {
        return $this->count;
    }

    private function clearInstanceCaches()
    {
        $this->cachedRequiredVersion = null;
    }
}
