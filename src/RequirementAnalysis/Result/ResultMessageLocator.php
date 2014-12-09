<?php

namespace Pvra\RequirementAnalysis\Result;


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
    private $inCallbackChain = false;
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

        if (empty($messageInfo['content']) && $runMissingMessageHandlers) {
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

        return !empty($messageInfo) ? $messageInfo : null;
    }

    private function inCallbackChain($areWeInCallbackChain)
    {
        $this->inCallbackChain = $areWeInCallbackChain;
    }

    private function isCallbackChainToBeTerminated()
    {
        return $this->terminateCallbackChain;
    }

    private function markCallbackChainTerminated()
    {
        $this->inCallbackChain(false);
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
            return false;
        } elseif (empty($msgInfo['content'])) {
            return null;
        } else {
            if ($msgInfo['fallbackHandler'] === false || $runTransformersOnFallbackHandler) {
                reset($this->transformers);
                $this->inCallbackChain(true);
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

    public function clearInstanceCache()
    {
        $this->fetchedMessages = [];
    }

    /**
     *
     */
    public function terminateCallbackChain()
    {
        if (!$this->inCallbackChain) {
            throw new \LogicException('A callback chain can only be terminated from within a callback.');
        }

        $this->terminateCallbackChain = true;
    }
}
