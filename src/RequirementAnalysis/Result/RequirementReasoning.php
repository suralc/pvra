<?php

namespace Pvra\RequirementAnalysis\Result;


use ArrayAccess;
use Pvra\RequirementAnalysis\RequirementAnalysisResult;

class RequirementReasoning implements ArrayAccess
{
    /**
     * @var int|string
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
     * @param int|string $reasonId
     * @param int $line
     * @param string $version
     * @param \Pvra\RequirementAnalysis\RequirementAnalysisResult $result
     * @param null|string $msg
     * @param array $data
     */
    public function __construct($reasonId, $line, $version, RequirementAnalysisResult $result, $msg = null, $data = [])
    {
        $this->reasonId = $reasonId;
        $this->line = $line;
        $this->msg = $msg;
        $this->result = $result;
        $this->data = $data;
        $this->version = $version;
    }

    /**
     * @return \Pvra\RequirementAnalysis\RequirementAnalysisResult
     */
    protected function getResult()
    {
        return $this->result;
    }

    /**
     * @param mixed $offset
     * @return bool|void
     */
    public function offsetExists($offset)
    {
        return in_array($offset, ['data', 'reason', 'reasonName', 'line', 'msg', 'version']);
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
            case 'msg': {
                if ($this->msg !== null) {
                    return $this->msg;
                }
                return $this->getResult()->getMsgFormatter()->getFormattedMessageFromId($this->reasonId,
                    array_merge($this->data, [
                        'line' => $this->line,
                        'reasonId' => $this->reasonId,
                        'targetId' => $this->getResult()->getAnalysisTargetId(),
                        'version' => $this->version
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
