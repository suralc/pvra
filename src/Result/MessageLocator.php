<?php
/**
 * MessageLocator.php
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
 * Class MessageLocator
 *
 * @package Pvra\Result
 */
class MessageLocator implements \ArrayAccess
{
    use CallbackChainHelperTrait;

    const CALLBACK_POSITION_PREPEND = 1,
        CALLBACK_POSITION_APPEND = 2;
    /**
     * @var callable[]
     */
    private $messageSearchers = [];
    /**
     * @var callable[]
     */
    private $missingMessageHandlers = [];
    /**
     * @var array
     */
    private $fetchedMessages = [];

    /**
     * Append a function to handle missing messages.
     *
     * The callbacks given to this method is executed when the corresponding MessageLocator
     * could not find a valid message for a given id.
     * The callback should return a string.
     * The callback may also prevent return false to allow subsequent callbacks to execute.
     * <code>
     * $locator->addMissingMessageHandler(function($msgId, MessageLocator $locator) {
     *     if(MyOwnLocator::canLocate($msgId)) {
     *         return $msg;
     *     }
     *     $locator->terminateCallbackChain();
     * });
     * </code>
     *
     * @param callable $locator
     * @param int $position
     * @return $this
     */
    public function addMissingMessageHandler(callable $locator, $position = self::CALLBACK_POSITION_APPEND)
    {
        if ($position === self::CALLBACK_POSITION_PREPEND) {
            array_unshift($this->missingMessageHandlers, $locator);
        } else {
            array_push($this->missingMessageHandlers, $locator);
        }

        return $this;
    }

    /**
     * @param string|int $messageId
     * @return bool
     * @throws \Exception
     */
    public function messageExists($messageId)
    {
        if (!self::validateMessageId($messageId)) {
            throw new \InvalidArgumentException('Only valid non-empty offset types are acceptable as message ids.');
        }

        if (!empty($this->fetchedMessages[ $messageId ])) {
            return true;
        }

        $msgInfo = $this->fetchMessage($messageId, false);

        return $msgInfo !== null && $msgInfo !== false && !empty($msgInfo['content']);
    }

    /**
     * @param string|int $mId
     * @return bool
     */
    private static function validateMessageId($mId)
    {
        return is_scalar($mId) && !empty($mId);
    }

    /**
     * @param string|int $messageId
     * @param bool $runMissingMessageHandlers
     * @return array|bool
     * @throws \Exception
     */
    protected function fetchMessage($messageId, $runMissingMessageHandlers = true)
    {
        $messageInfo = [
            'id' => $messageId,
            'content' => null,
            'fallbackHandler' => false,
        ];


        reset($this->messageSearchers);
        $this->inCallbackChain(true);
        /** @var callable $searchCallback */
        foreach ($this->messageSearchers as $searchCallback) {
            if ($this->isCallbackChainToBeTerminated()) {
                break;
            }
            $value = $searchCallback($messageId, $this);
            if (!empty($value) && is_string($value)) {
                $messageInfo['id'] = $messageId;
                $messageInfo['content'] = $value;
                break;
            }
        }
        $this->markCallbackChainTerminated();

        if (empty($messageInfo['content'])) {
            if ($runMissingMessageHandlers) {
                $this->inCallbackChain(true);
                reset($this->missingMessageHandlers);
                /** @var callable $handler */
                foreach ($this->missingMessageHandlers as $handler) {
                    if ($this->isCallbackChainToBeTerminated()) {
                        break;
                    }
                    $value = $handler($messageId, $this);
                    if (!empty($value) && is_string($value)) {
                        $messageInfo['id'] = $messageId;
                        $messageInfo['content'] = $value;
                        $messageInfo['fallbackHandler'] = true;
                        break;
                    }
                }
                $this->markCallbackChainTerminated();
            }
        } else {
            $this->fetchedMessages[ $messageId ] = $messageInfo;
        }

        return $messageInfo;
    }


    /**
     * @param int|string $messageId
     * @param bool $ignoreCachedEntries
     * @return string|null
     * @throws \Exception
     */
    public function getMessage($messageId, $ignoreCachedEntries = false)
    {
        $info = $this->getMessageWithInfo($messageId, $ignoreCachedEntries);
        return is_array($info) && array_key_exists('content', $info) ? $info['content'] : $info;
    }

    /**
     * @param int|string $messageId
     * @param bool $ignoreCachedEntries
     * @return array
     */
    public function getMessageWithInfo($messageId, $ignoreCachedEntries = false)
    {
        if ($ignoreCachedEntries !== true
            && !empty($this->fetchedMessages[ $messageId ])
        ) {
            return $this->fetchedMessages[ $messageId ];
        }

        return $this->fetchMessage($messageId);
    }

    /**
     * @param string $path
     * @return \Pvra\Result\MessageLocator
     */
    public static function fromPhpFile($path)
    {
        if(is_file($path) && is_readable($path)) {
            return static::fromArray(require $path);
        }

        throw new \InvalidArgumentException('Invalid path or not readable.');
    }

    public static function fromJsonFile($file)
    {
        if (is_readable($file) && is_file($file)) {
            if (($jsonData = json_decode(file_get_contents($file), true)) !== null) {
                return static::fromArray($jsonData);
            } else {
                throw new \InvalidArgumentException(sprintf("%s could not be parsed as json file. Last json error message was\n%s",
                    $file, version_compare(PHP_VERSION, '5.5.0', '>=') ? json_last_error_msg() : json_last_error()));
            }
        } else {
            throw new \InvalidArgumentException(sprintf('%s is not a valid file', $file));
        }
    }

    /**
     * @param $array
     * @return static
     */
    public static function fromArray($array)
    {
        $locator = new static();
        $locator->addMessageSearcher(function ($msgId) use ($array) {
            if (isset($array[ $msgId ])) {
                return $array[ $msgId ];
            }

            return false;
        }, self::CALLBACK_POSITION_PREPEND);

        return $locator;
    }

    /**
     * @param callable $searcher
     * @param int $position
     * @return $this
     */
    public function addMessageSearcher(callable $searcher, $position = self::CALLBACK_POSITION_APPEND)
    {
        if ($position === self::CALLBACK_POSITION_PREPEND) {
            array_unshift($this->messageSearchers, $searcher);
        } else {
            array_push($this->messageSearchers, $searcher);
        }

        return $this;
    }

    /**
     * @codeCoverageIgnore
     */
    public function clearInstanceCache()
    {
        $this->fetchedMessages = [];
    }


    /**
     * OffsetExists
     * Whether a message with the given id can be found without invoking missing
     * message handlers
     *
     * @link http://php.net/manual/en/arrayaccess.offsetexists.php
     * @param string|int $id message id
     * @return boolean true on success or false on failure.
     */
    public function offsetExists($id)
    {
        return $this->messageExists($id);
    }

    /**
     * OffsetGet
     * This method proxies ::getMessage($offset)
     *
     * @link http://php.net/manual/en/arrayaccess.offsetget.php
     * @param string|int $id the id to retrieve
     * @return string the message template
     */
    public function offsetGet($id)
    {
        return $this->getMessage($id);
    }

    /**
     * Appends a new message searcher
     * Only the following syntax is valid: `$locator[] = function($id, $locator) {};
     *
     * @param null $offset
     * @param callable $value
     * @throws \InvalidArgumentException Exception is thrown if an offset is specified
     * or the value is not callable
     */
    public function offsetSet($offset, $value)
    {
        if ($offset === null && is_callable($value)) {
            $this->addMessageSearcher($value);
        } else {
            throw new \InvalidArgumentException('This is  an obscure syntax that might be removed later.');
        }
    }

    /**
     * @param mixed $offset
     * @throws \RuntimeException Is always thrown as this operation is not supported
     */
    public function offsetUnset($offset)
    {
        throw new \RuntimeException('This operation is  unsupported.');
    }
}
