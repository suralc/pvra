<?php

namespace Pvra\RequirementAnalysis\Result;


class ResultMessageFormatter
{
    /**
     * @var ResultMessageLocator
     */
    private $locator;
    private $throwOnMissingTemplate = false;

    /**
     * @param array|ResultMessageLocator $locator
     * @param bool $throwOnMissingTemplate
     */
    public function __construct($locator = null, $throwOnMissingTemplate = false)
    {
        $this->throwOnMissingTemplate = $throwOnMissingTemplate;
        if ($locator instanceof ResultMessageLocator) {
            $this->locator = $locator;
        } elseif ($locator === null) {
            $this->locator = ResultMessageLocator::fromPhpFile(__DIR__ . '/../../../data/default_messages.php');
        } elseif (is_array($locator)) {
            $this->locator = ResultMessageLocator::fromArray($locator);
        } else {
            throw new \InvalidArgumentException('The $locator parameter needs to be an instance of ResultMessageLoator, null or an array containing messages');
        }
    }

    public function format($message, array $data)
    {
        foreach ($data as $name => $value) {
            // str_replace(array, array) would be better
            // but this appears to be faster than iterating over the $data array and manipulating the keys
            $message = str_replace(':' . $name . ':', $value, $message);
        }

        return $message;
    }

    public function getFormattedMessageFromId($msgId, array $data)
    {
        return $this->format($this->getMessageTemplate($msgId), $data);
    }

    public function getMessageTemplate($msgId)
    {
        if ($this->getLocator()->messageExists($msgId)) {
            $msg = $this->locator[ $msgId ];
        } else {
            if ($this->throwOnMissingTemplate) {
                throw new \Exception(sprintf('Could not find message for id: "%s"', $msgId));
            }
            $msg = 'Could not find message template for "' .
                ($r = RequirementReason::getReasonNameFromValue($msgId)) !== 'UNKNOWN' ? $r : $msgId . '"';
        }

        return $msg;
    }

    public function messageForIdExists($msgId)
    {
        return false;//isset($this->locator[ $msgId ]);
    }

    /**
     * @return \Pvra\RequirementAnalysis\Result\ResultMessageLocator
     */
    protected function getLocator()
    {
        return $this->locator;
    }
}
