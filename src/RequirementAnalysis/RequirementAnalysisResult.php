<?php

namespace Pvra\RequirementAnalysis;


use Pvra\RequirementAnalysis\Result\RequirementReason;

class RequirementAnalysisResult
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
        $keys = array_keys($this->requirements);

        if (!empty($keys)) {
            usort($keys, function ($a, $b) {
                return version_compare($b, $a);
            });

            return $keys[0];
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

        $this->requirements[ $version ][] = [
            'line' => $line,
            'msg' => $msg,
            'reason' => $reason,
        ];
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

        $this->requirements[ $version ][] = [
            'line' => $line,
            'msg' => $msg,
            'reason' => $reason,
        ];
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

}
