<?php
/**
 * AnalysisResult.php
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


use Pvra\Result\MessageFormatter;
use Pvra\Result\Reason;
use Pvra\Result\Reasoning;

/**
 * Class AnalysisResult
 *
 * @package Pvra
 */
class AnalysisResult implements \IteratorAggregate, \Countable
{
    const INITIAL_ANALYSIS_TARGET_ID = 'unknown';
    const VERSION_LIMIT_MIN = 0;
    const VERSION_LIMIT_MAX = 1;

    /**
     * The state of this instance
     *
     * @var bool
     */
    private $isSealed = false;

    /**
     * @var string Filename or hash of input string
     */
    private $analysisTargetId = self::INITIAL_ANALYSIS_TARGET_ID;

    /**
     * @var array|Reasoning[]
     */
    private $requirements = [];

    /**
     * @var array|Reasoning[]
     */
    private $limits = [];

    /**
     * @var string|null
     */
    private $cachedVersionRequirement;

    /**
     * @var string|null
     */
    private $cachedVersionLimit;

    /**
     * @var int
     */
    private $cachedRequiredVersionId;

    /**
     * @var int
     */
    private $cachedLimitedVersionId;

    /**
     * Number of attached `Reasonings` instances.
     *
     * @var int
     */
    private $count = 0;

    /**
     * @var MessageFormatter
     */
    private $msgFormatter;

    /**
     * Calculate the id of the required version
     *
     * Creates an integer representation of a version in the format "a.b[.c]".
     * The third version element is optional and can be omitted. A default value of "0" will be
     * assumed.
     *
     * @return int
     * @throws \Exception
     */
    public function getRequiredVersionId()
    {
        if ($this->cachedRequiredVersionId === null) {
            $this->cachedRequiredVersionId = $this->calculateVersionIdFromString($this->getRequiredVersion());
        }

        return $this->cachedRequiredVersionId;
    }

    public function getVersionLimitId()
    {
        if ($this->cachedLimitedVersionId === null) {
            $this->cachedLimitedVersionId = $this->calculateVersionIdFromString($this->getVersionLimit());
        }

        return $this->cachedLimitedVersionId;
    }

    /**
     * @param string $version
     * @return int
     * @throws \Exception
     */
    private function calculateVersionIdFromString($version)
    {
        $versionComponents = explode('.', $version);

        $elementCount = count($versionComponents);
        if ($elementCount > 3 || $elementCount < 2) {
            throw new \Exception(sprintf('A version id has to be built from two or three segments. "%s" is not valid.',
                $version));
        }

        $versionComponents += [2 => 0];

        return $versionComponents[0] * 10000 + $versionComponents[1] * 100 + $versionComponents[2];
    }

    /**
     * Get the attached message formatter
     *
     * If no message formatter has been set a default one will be created assuming default values.
     *
     * @return \Pvra\Result\MessageFormatter
     */
    public function getMsgFormatter()
    {
        if ($this->msgFormatter === null) {
            $this->msgFormatter = new MessageFormatter();
        }

        return $this->msgFormatter;
    }

    /**
     * @param \Pvra\Result\MessageFormatter $formatter
     * @return $this
     */
    public function setMsgFormatter(MessageFormatter $formatter)
    {
        $this->msgFormatter = $formatter;

        return $this;
    }

    /**
     * Retrieve the required version
     *
     * This method calculates the highest required version of all known requirements.
     * If no changes were made between the calls to this method the version requirement will
     * not be recalculated.
     *
     * @return string The required version in the format `Major.Minor[.Patch]`
     * @see http://php.net/manual/en/function.version-compare.php version_compare()
     */
    public function getRequiredVersion()
    {
        if ($this->cachedVersionRequirement !== null) {
            return $this->cachedVersionRequirement;
        }

        $keys = array_keys($this->requirements);

        if (!empty($keys)) {
            usort($keys, function ($a, $b) {
                return version_compare($b, $a);
            });

            return $this->cachedVersionRequirement = $keys[0];
        }

        return '5.3.0';
    }

    /**
     * Retrieve the upper version limit
     *
     * This method calculates the upper version limit of all known reasonings.
     * If no changes were made between the calls to this method the version limit will
     * not be recalculated.
     *
     * @return string The version limit in the format `Major.Minor[.Patch]`
     * @see http://php.net/manual/en/function.version-compare.php version_compare()
     */
    public function getVersionLimit()
    {
        if ($this->cachedVersionLimit !== null) {
            return $this->cachedVersionLimit;
        }

        $keys = array_keys($this->limits);

        if (!empty($keys)) {
            usort($keys, function ($a, $b) {
                return version_compare($a, $b);
            });

            return $this->cachedVersionLimit = $keys[0];
        }

        return '7.0.0';
    }

    /**
     * Add an arbitrary requirement identified by version
     *
     * This method can be used to add an arbitrary requirement. All parameters but the first are optional
     *
     * @param string $version The version in the format `Major.Minor[.Patch]`
     * @param int $line The line that caused the requirement.
     * @param string $msg The message template that should be used. If `null` is passed the attached `MessageLocator`
     *     will be called to retrieve a template based on the `$reason` parameter.
     * @param int $reason The reason for this requirement. Please be aware: Setting this parameter will **not**
     *     override the required version
     * @param array $data Additional data that should be passed to the message formatter.
     * @return $this
     */
    public function addArbitraryRequirement(
        $version,
        $line = -1,
        $msg = null,
        $reason = Reason::UNKNOWN,
        array $data = []
    ) {
        $this->addArbitraryVersionConstraint(self::VERSION_LIMIT_MAX, $version, $line, $msg, $reason,
            $data);

        return $this;
    }

    /**
     * Add a requirement identified by reason id
     *
     * This method can be used to add a requirement that is identified by its reason id.
     *
     * @param int $reason The reason for this requirement. The required version is determined from this id.
     * @param int $line The line that caused the requirement.
     * @param string $msg The message template that should be used. If `null` is passed the attached `MessageLocator`
     *     will be called to retrieve a template based on the `$reason` parameter.
     * @param array $data Additional data that should be passed to the message formatter.
     * @return $this
     * @throws \LogicException Thrown if the reason is unknown or does not have a version requirement associated.
     */
    public function addRequirement($reason, $line = -1, $msg = null, array $data = [])
    {
        $version = Reason::getVersionFromReason($reason);

        if ($version === false) {
            throw new \LogicException(sprintf('%s::%s requires a reason a version can be associated to. Use %s::addArbitraryRequirement() to add any version with any reasoning to the result.',
                __CLASS__, __METHOD__, __CLASS__));
        }

        $this->addArbitraryVersionConstraint(self::VERSION_LIMIT_MAX, $version, $line, $msg, $reason,
            $data);

        return $this;
    }

    /**
     * @param int $reason
     * @param int $line
     * @param null|string $msg
     * @param array $data
     * @return $this
     */
    public function addLimit($reason, $line = -1, $msg = null, array $data = [])
    {
        $version = Reason::getVersionFromReason($reason);

        if ($version === false) {
            throw new \LogicException(sprintf('%s::%s requires a reason a version can be associated to. Use %s::addArbitraryLimit() to add any version with any reasoning to the result.',
                __CLASS__, __METHOD__, __CLASS__));
        }

        $this->addArbitraryVersionConstraint(self::VERSION_LIMIT_MIN, $version, $line, $msg, $reason,
            $data);

        return $this;
    }

    /**
     * @param string $version
     * @param int $line
     * @param null|string $msg
     * @param int $reason
     * @param array $data
     * @return $this
     */
    public function addArbitraryLimit(
        $version,
        $line = -1,
        $msg = null,
        $reason = Reason::UNKNOWN,
        array $data = []
    ) {
        $this->addArbitraryVersionConstraint(self::VERSION_LIMIT_MIN, $version, $line, $msg, $reason,
            $data);

        return $this;
    }

    /**
     * @param int $type
     * @param string $version
     * @param int $line
     * @param null|string $msg
     * @param int $reason
     * @param array $data
     */
    protected function addArbitraryVersionConstraint(
        $type,
        $version,
        $line = -1,
        $msg = null,
        $reason = Reason::UNKNOWN,
        array $data = []
    ) {
        if ($this->isSealed()) {
            throw new \RuntimeException('Impossible to write to already sealed result');
        }

        $this->clearInstanceCaches();
        $this->count++;

        if ($type === self::VERSION_LIMIT_MAX) {
            $this->requirements[ $version ][] = new Reasoning($reason, $line, $this, $version, $msg, $data);
        } elseif ($type === self::VERSION_LIMIT_MIN) {
            $this->limits[ $version ][] = new Reasoning($reason, $line, $this, $version, $msg, $data);
        }
    }

    /**
     * @return bool
     */
    public function isSealed()
    {
        return $this->isSealed;
    }

    /**
     * @return array|\Pvra\Result\Reasoning[]
     */
    public function getRequirements()
    {
        return $this->requirements;
    }

    /**
     * @return array|\Pvra\Result\Reasoning[]
     */
    public function getLimits()
    {
        return $this->limits;
    }

    /**
     * Get all reasonings related to a version
     *
     * If no reasoning for a version is known an empty array will be returned.
     *
     * @param string $version Version in the format `Major.Minor.Patch`
     * @return array|Reasoning[] List of `Reasoning` or empty array
     */
    public function getRequirementInfo($version)
    {
        if (isset($this->requirements[ $version ])) {
            return $this->requirements[ $version ];
        }

        return [];
    }

    /**
     * @param string $version
     * @return array|\Pvra\Result\Reasoning[]
     */
    public function getLimitInfo($version)
    {
        if (isset($this->limits[ $version ])) {
            return $this->limits[ $version ];
        }

        return [];
    }

    /**
     * Get the current analysis target id
     *
     * @return string Analysis target id
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
        if ($this->isSealed() || $this->getAnalysisTargetId() !== self::INITIAL_ANALYSIS_TARGET_ID) {
            throw new \RuntimeException('You cannot modify an already set or sealed result.');
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
     * @return \ArrayIterator|array|Reasoning[]
     */
    public function getIterator()
    {
        $iterator = new \ArrayIterator();
        $data = [$this->getRequirements(), $this->getLimits()];
        array_walk_recursive($data, function ($value) use ($iterator) {
            if ($value instanceof Reasoning) {
                $iterator->append($value);
            }
        });

        return $iterator;
    }

    /**
     * @return \ArrayIterator
     */
    public function getLimitIterator()
    {
        $iterator = new \ArrayIterator();
        foreach ($this->getLimits() as $version) {
            /** @var Reasoning $item */
            foreach ($version as $item) {
                if ($item instanceof Reasoning) {
                    $iterator->append($item);
                }
            }
        }

        return $iterator;
    }

    /**
     * @return \ArrayIterator
     */
    public function getRequirementIterator()
    {
        $iterator = new \ArrayIterator();
        foreach ($this->getRequirements() as $version) {
            /** @var Reasoning $item */
            foreach ($version as $item) {
                if ($item instanceof Reasoning) {
                    $iterator->append($item);
                }
            }
        }

        return $iterator;
    }

    /**
     * Number of registered reasonings
     *
     * @return int
     */
    public function count()
    {
        return $this->count;
    }

    /**
     * Clear the cached max version requirements
     */
    private function clearInstanceCaches()
    {
        $this->cachedVersionRequirement = null;
        $this->cachedRequiredVersionId = null;
        $this->cachedLimitedVersionId = null;
        $this->cachedVersionLimit = null;
    }
}
