<?php
/**
 * RequirementReasoning.php
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


use ArrayAccess;
use Pvra\RequirementAnalysis\RequirementAnalysisResult;

/**
 * Class RequirementReasoning
 *
 * @package Pvra\RequirementAnalysis\Result
 */
class RequirementReasoning implements ArrayAccess
{
    /**
     * The reason this reasoning maps to
     *
     * This may be an arbitrary scalar but is most likely to map to a known constant in `RequirementReason`.
     *
     * @var int|string
     * @see RequirementReason Possible mapping target
     */
    private $reasonId;
    /**
     * @var int
     */
    private $line;
    /**
     * @var null|string
     */
    private $msg;
    /**
     * @var \Pvra\RequirementAnalysis\RequirementAnalysisResult
     */
    private $result;
    /**
     * @var array
     */
    private $data;
    /**
     * @var string
     */
    private $version;

    /**
     * RequirementReasoning constructor
     *
     * Used to construct this reasoning. Only the `reasonId`, `line` and `result` parameters
     * are required. The remaining parameters can be determined based on the reasonId and result instance.     *
     *
     * @param int|string $reasonId The mapped reasonId
     * @param int $line The mapped line
     * @param \Pvra\RequirementAnalysis\RequirementAnalysisResult $result The result this reasoning applies to
     * @param string|null $version The required version.
     * @param null|string $msg The message related to this reasoning. If this parameter is set to `null` the message is
     * fetched from the `MessageLocator` attached to the result instance related to this instance.
     * @param array $data An array of additional data passed to the `ResultMessageFormatter`
     */
    public function __construct(
        $reasonId,
        $line,
        RequirementAnalysisResult $result,
        $version = null,
        $msg = null,
        $data = []
    ) {
        $this->reasonId = $reasonId;
        $this->line = $line;
        $this->msg = $msg;
        $this->result = $result;
        $this->data = $data;
        if ($version === null) {
            $this->version = RequirementReason::getRequiredVersionForReason($reasonId);
        } else {
            $this->version = $version;
        }
    }

    /**
     * Get the related result
     * @return \Pvra\RequirementAnalysis\RequirementAnalysisResult
     */
    protected function getResult()
    {
        return $this->result;
    }

    /**
     * @param int|string $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        return in_array($offset, ['data', 'reason', 'reasonName', 'line', 'msg', 'raw_msg', 'version']);
    }

    /**
     * @param mixed $offset
     * @return array|int|null|string
     */
    public function offsetGet($offset)
    {
        switch ($offset) {
            case 'data':
                return $this->data;
            case 'line':
                return $this->line;
            case 'version':
                return $this->version;
            case 'reason':
                return $this->reasonId;
            case 'reasonName':
                return RequirementReason::getReasonNameFromValue($this->reasonId);
            case 'raw_msg': {
                if ($this->msg !== null) {
                    return $this->msg;
                }
                return $this->getResult()->getMsgFormatter()->getLocator()->getMessage($this->reasonId);
            }
            case 'msg': {
                if ($this->msg !== null) {
                    return $this->msg;
                }
                return $this->getResult()->getMsgFormatter()->getFormattedMessageFromId($this->reasonId,
                    array_merge($this->data, [
                        ResultMessageFormatter::FORMAT_KEY_LINE => $this->line,
                        ResultMessageFormatter::FORMAT_KEY_REASON_ID => $this->reasonId,
                        ResultMessageFormatter::FORMAT_KEY_TARGET_ID => $this->getResult()->getAnalysisTargetId(),
                        ResultMessageFormatter::FORMAT_KEY_VERSION => $this->version,
                        ResultMessageFormatter::FORMAT_KEY_REASON_NAME => $this->offsetGet('reasonName'),
                    ]));
            }
        }

        return null;
    }

    /**
     * @param mixed $offset
     * @param mixed $value
     * @throws \Exception
     */
    public function offsetSet($offset, $value)
    {
        throw new \Exception('Unsupported operation.');
    }

    /**
     * @param mixed $offset
     * @throws \Exception
     */
    public function offsetUnset($offset)
    {
        throw new \Exception('Unsupported operation.');
    }
}