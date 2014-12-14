<?php

namespace Pvra\RequirementAnalysis\Result;


class ResultMessageFormatter
{
    use CallbackChainHelperTrait;

    const CALLBACK_POSITION_PREPEND = 1,
        CALLBACK_POSITION_APPEND = 2;

    const FORMAT_KEY_TARGET_ID = 'targetId',
        FORMAT_KEY_LINE = 'line',
        FORMAT_KEY_VERSION = 'version',
        FORMAT_KEY_REASON_ID = 'reasonId',
        FORMAT_KEY_REASON_NAME = 'reasonName';

    /**
     * @var ResultMessageLocator
     */
    private $locator;
    private $throwOnMissingTemplate = false;
    private $messageFormatters = [];

    /**
     * @param array|ResultMessageLocator $locator
     * @param bool $addDefaultFormatter
     * @param bool $addDefaultExclusiveMissingMessageLocatorHandler
     * @param bool $throwOnMissingTemplate
     */
    public function __construct(
        $locator = null,
        $addDefaultFormatter = true,
        $addDefaultExclusiveMissingMessageLocatorHandler = false,
        $throwOnMissingTemplate = false
    ) {
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

        if ($addDefaultFormatter) {
            $this->addMessageFormatter(function ($id, $format) {
                return sprintf('%s in :%s:::%s:', $format, ResultMessageFormatter::FORMAT_KEY_TARGET_ID,
                    ResultMessageFormatter::FORMAT_KEY_LINE);
            });
        }

        if ($addDefaultExclusiveMissingMessageLocatorHandler) {
            $this->getLocator()->addMissingMessageHandler(function ($id, ResultMessageLocator $locator) {
                $locator->terminateCallbackChain();
                $msg = 'Message for id "%s" %s could not be found.';
                $desc = '';
                if (($name = RequirementReason::getReasonNameFromValue((int)$id)) !== 'UNKNOWN') {
                    $desc = '[' . $name . ']';
                }
                return sprintf($msg, $id, $desc);
            }, ResultMessageLocator::CALLBACK_POSITION_PREPEND);
        }
    }

    /**
     * @param callable $transformer A callback with the following signature:
     * ```
     * function(int|string $msgId, string $template, ResultMessageFormatter $f, array $data) : string
     * ```
     * @param int $position
     * @return $this
     */
    public function addMessageFormatter(callable $transformer, $position = self::CALLBACK_POSITION_APPEND)
    {
        if ($position === self::CALLBACK_POSITION_PREPEND) {
            array_unshift($this->messageFormatters, $transformer);
        } else {
            array_push($this->messageFormatters, $transformer);
        }

        return $this;
    }

    public function getFormattedMessageFromId($msgId, array $data = [])
    {
        return $this->format(['id' => $msgId, 'template' => $this->getMessageTemplate($msgId)], $data);
    }

    public function format($messageInfo, array $data = [], $runUserFormatters = true)
    {
        if (is_string($messageInfo)) {
            $messageInfo = ['template' => $messageInfo, 'id' => 'unknown_message_id'];
        }
        $data += ['id' => $messageInfo['id']];
        if ($runUserFormatters) {
            reset($this->messageFormatters);
            $this->inCallbackChain(true);
            /** @var callable $formatter */
            foreach ($this->messageFormatters as $formatter) {
                if ($this->isCallbackChainToBeTerminated()) {
                    break;
                }
                $messageInfo['template'] = $formatter($messageInfo['id'], $messageInfo['template'], $this, $data);
            }
            $this->markCallbackChainTerminated();
        }

        foreach ($data as $name => $value) {
            // str_replace(array, array) would be better
            // but this appears to be faster than iterating over the $data array and manipulating the keys
            $messageInfo['template'] = str_replace(':' . $name . ':', $value, $messageInfo['template']);
        }

        return $messageInfo['template'];
    }

    public function getMessageTemplate($msgId)
    {
        if ($this->throwOnMissingTemplate && !$this->getLocator()->messageExists($msgId)) {
            throw new \Exception(sprintf('Could not find message for id: "%s"', $msgId));
        } else {
            return $this->getLocator()->getMessage($msgId);
        }
    }

    /**
     * @return \Pvra\RequirementAnalysis\Result\ResultMessageLocator
     */
    public function getLocator()
    {
        return $this->locator;
    }

    public function messageForIdExists($msgId)
    {
        return $this->getLocator()->messageExists($msgId);
    }
}
