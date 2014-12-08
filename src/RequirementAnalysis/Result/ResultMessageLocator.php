<?php

namespace Pvra\RequirementAnalysis\Result;


use Pvra\RequirementAnalysis\Result\Exceptions\MessageLocationNeedsToBeTerminatedException;

class ResultMessageLocator
{
    const CALLBACK_POSITION_PREPEND = 1,
        CALLBACK_POSITION_APPEND = 2;

    private $defaultSuffix = ' in :targetId:::line:';
    /**
     * @var callable[]
     */
    private $messageSearchers = [];
    /**
     * @var callable[]
     */
    private $missingMessageHandlers = [];
    /**
     * @var callable[]
     */
    private $transformers = [];
    private $terminateCallbackChain = false;
    private $fetchedMessages = [];

    /**
     * @param bool $addDefaultSuffix
     */
    public function __construct($addDefaultSuffix = true)
    {
        if ($addDefaultSuffix) {
            $this->addTransformer(function ($id, $format) {
                $format .= $this->defaultSuffix;
                return $format;
            });
        }
    }

    /**
     * @param callable $transformer function($messageId, $messageFormat, ResultMessageLocator $loc) : array($newFormat)
     * @param int $position
     * @return $this
     */
    public function addTransformer(callable $transformer, $position = self::CALLBACK_POSITION_APPEND)
    {
        if ($position === self::CALLBACK_POSITION_PREPEND) {
            array_unshift($this->transformers, $transformer);
        } else {
            array_push($this->transformers, $transformer);
        }

        return $this;
    }

    /**
     * Append a function to handle missing messages.
     *
     * The callbacks given to this method is executed when the corresponding ResultMessageLocator
     * could not find a valid message for a given id.
     * The callback should return a string.
     * The callback may also prevent return false to allow subsequent callbacks to execute.
     * <code>
     * $locator->addMissingMessageHandler(function($msgId, ResultMessageLocator $locator) {
     *     if(MyOwnLocator::canLocate($msgId)) {
     *         return $msg;
     *     }
     *     $locator->terminateCallbackChain();
     * });
     * </code>
     * @param callable $locator
     * @param int $position
     */
    public function addMissingMessageHandler(callable $locator, $position = self::CALLBACK_POSITION_APPEND)
    {
        if ($position === self::CALLBACK_POSITION_PREPEND) {
            array_unshift($this->missingMessageHandlers, $locator);
        } else {
            array_push($this->missingMessageHandlers, $locator);
        }
    }

    /**
     * @param string|int $messageId
     * @return bool
     * @throws \Exception
     */
    public function messageExists($messageId)
    {
        if (!$this->isValidMessageId($messageId)) {
            throw new \InvalidArgumentException('Only valid non-empty offset types are acceptable as message ids.');
        }

        if (isset($this->fetchedMessages[ $messageId ]) && !empty($this->fetchedMessages[ $messageId ])) {
            return true;
        }

        $msgInfo = $this->fetchMessage($messageId, false);

        return $msgInfo != false && !empty($msgInfo['content']);
    }

    private function isValidMessageId($mId)
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

        try {
            reset($this->messageSearchers);
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
        } catch (MessageLocationNeedsToBeTerminatedException $ex) {
            return false;
        } catch (\Exception $e) {
            throw $e;
        }

        if (empty($messageInfo) && $runMissingMessageHandlers) {
            /** @var callable $handler */
            reset($this->missingMessageHandlers);
            $messageInfo['fallbackHandler'] = true;
            foreach ($this->missingMessageHandlers as $handler) {
                if ($this->isCallbackChainToBeTerminated()) {
                    break;
                }
                $value = $handler($messageId, $this);
                // todo impelemtatnon
            }
            $this->markCallbackChainTerminated();
        }

        return !empty($messageInfo) ? $messageInfo : false;
    }

    private function isCallbackChainToBeTerminated()
    {
        return $this->terminateCallbackChain;
    }

    private function markCallbackChainTerminated()
    {
        $this->terminateCallbackChain = false;
    }

    /**
     * @param int|string $messageId
     * @param bool $runTransformersOnFallbackHandler
     * @param bool $ignoreCachedEntries
     * @return array|bool|null
     * @throws \Exception
     */
    public function getMessage($messageId, $runTransformersOnFallbackHandler = true, $ignoreCachedEntries = false)
    {
        if ($ignoreCachedEntries !== true
            && !empty($this->fetchedMessages[ $messageId ])
        ) {
            return $this->fetchedMessages[ $messageId ];
        }

        $msgInfo = $this->fetchMessage($messageId);

        if ($msgInfo === false) {
            // exception, might need rework
            return $msgInfo;
        } elseif (empty($msgInfo['content'])) {
            return null;
        } else {
            if ($msgInfo['fallbackHandler'] === false || $runTransformersOnFallbackHandler) {
                reset($this->transformers);
                /** @var callable $transformer */
                foreach ($this->transformers as $transformer) {
                    if ($this->isCallbackChainToBeTerminated()) {
                        break;
                    }
                    $msgInfo['content'] = $transformer($messageId, $msgInfo['content'], $this);
                }
                $this->markCallbackChainTerminated();
            }

            return $msgInfo['content'];
        }
    }

    /**
     * @param $string
     * @return \Pvra\RequirementAnalysis\Result\ResultMessageLocator
     */
    public static function fromPhpFile($string)
    {
        return static::fromArray(require $string);
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
     *
     * Let the callback throw a MessageLocationNeedsToBeTerminatedException if search needs to be terminated
     * for whatever reason
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
     *
     */
    public function clearInstanceCache()
    {
        $this->fetchedMessages = [];
    }

    /**
     *
     */
    public function terminateCallbackChain()
    {
        $this->terminateCallbackChain = true;
    }
}
