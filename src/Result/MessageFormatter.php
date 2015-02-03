<?php
/**
 * MessageFormatter.php
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
namespace Pvra\Result;


/**
 * Class MessageFormatter
 *
 * @package Pvra\Result
 */
class MessageFormatter
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
     * @var MessageLocator
     */
    private $locator;
    /**
     * @var bool
     */
    private $throwOnMissingTemplate = false;
    /**
     * @var array
     */
    private $messageFormatters = [];

    /**
     * @param array|MessageLocator $locator
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
        if ($locator instanceof MessageLocator) {
            $this->locator = $locator;
        } elseif ($locator === null) {
            $this->locator = MessageLocator::fromPhpFile(__DIR__ . '/../../data/default_messages.php');
        } elseif (is_array($locator)) {
            $this->locator = MessageLocator::fromArray($locator);
        } else {
            throw new \InvalidArgumentException('The $locator parameter needs to be an instance of ResultMessageLoator, null or an array containing messages');
        }

        if ($addDefaultFormatter) {
            $this->addMessageFormatter(function ($id, $format) {
                return sprintf('%s in :%s:::%s:', $format, MessageFormatter::FORMAT_KEY_TARGET_ID,
                    MessageFormatter::FORMAT_KEY_LINE);
            });
        }

        if ($addDefaultExclusiveMissingMessageLocatorHandler) {
            $this->getLocator()->addMissingMessageHandler(function ($id, MessageLocator $locator) {
                $locator->terminateCallbackChain();
                $msg = 'Message for id "%s" %s could not be found.';
                $desc = '';
                if (($name = Reason::getReasonNameFromValue((int)$id)) !== 'UNKNOWN') {
                    $desc = '[' . $name . ']';
                }
                return sprintf($msg, $id, $desc);
            }, MessageLocator::CALLBACK_POSITION_PREPEND);
        }
    }

    /**
     * @param callable $transformer A callback with the following signature:
     * ```
     * function(int|string $msgId, string $template, MessageFormatter $f, array $data) : string
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

    /**
     * @param $msgId
     * @param array $data
     * @return mixed
     * @throws \Exception
     */
    public function getFormattedMessageFromId($msgId, array $data = [])
    {
        return $this->format(['id' => $msgId, 'template' => $this->getMessageTemplate($msgId)], $data);
    }

    /**
     * @param $messageInfo
     * @param array $data
     * @param bool $runUserFormatters
     * @return mixed
     */
    public function format($messageInfo, array $data = [], $runUserFormatters = true)
    {
        if (is_string($messageInfo)) {
            $messageInfo = ['template' => $messageInfo, 'id' => 'unknown_message_id'];
        }
        $data += ['id' => $messageInfo['id']];
        if ($runUserFormatters) {
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

    /**
     * @param $msgId
     * @return null|string
     * @throws \Exception
     */
    public function getMessageTemplate($msgId)
    {
        if ($this->throwOnMissingTemplate && !$this->getLocator()->messageExists($msgId)) {
            throw new \Exception(sprintf('Could not find message for id: "%s"', $msgId));
        } else {
            return $this->getLocator()->getMessage($msgId);
        }
    }

    /**
     * @return \Pvra\Result\MessageLocator
     */
    public function getLocator()
    {
        return $this->locator;
    }

    /**
     * @param $msgId
     * @return bool
     */
    public function messageForIdExists($msgId)
    {
        return $this->getLocator()->messageExists($msgId);
    }
}
