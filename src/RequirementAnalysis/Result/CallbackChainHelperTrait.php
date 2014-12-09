<?php

namespace Pvra\RequirementAnalysis\Result;


trait CallbackChainHelperTrait
{
    private $terminateCallbackChain = false;
    private $inCallbackChain = false;

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

    protected function inCallbackChain($areWeInCallbackChain)
    {
        $this->inCallbackChain = $areWeInCallbackChain;
    }

    protected function isCallbackChainToBeTerminated()
    {
        return $this->terminateCallbackChain;
    }

    protected function markCallbackChainTerminated()
    {
        $this->inCallbackChain(false);
        $this->terminateCallbackChain = false;
    }
}
