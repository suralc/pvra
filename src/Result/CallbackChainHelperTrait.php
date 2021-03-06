<?php
/**
 * CallbackChainHelperTrait.php
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
 * @author     suralc <suralc.github@gmail.com>
 * @license    http://opensource.org/licenses/MIT  MIT
 */
namespace Pvra\Result;

/**
 * Class CallbackChainHelperTrait
 *
 * @package Pvra\Result
 */
trait CallbackChainHelperTrait
{
    /**
     * Determines if the current callback-chain should be terminated at the
     * next iteration
     *
     * @var bool
     */
    private $terminateCallbackChain = false;
    /**
     * Callback execution status
     *
     * @var bool
     */
    private $inCallbackChain = false;

    /**
     * Terminate callback chain from inside a callback
     *
     * This method may be called from inside of a callback chain to break out in the next iteration     *
     *
     * @throws \LogicException Thrown if no callback chain is executing when called
     */
    public function terminateCallbackChain()
    {
        if (!$this->inCallbackChain) {
            throw new \LogicException('A callback chain can only be terminated from within a callback.');
        }

        $this->terminateCallbackChain = true;
    }

    /**
     * Modify the status of current callback execution
     *
     * This method can be used by the callback calling code to make sure other methods react
     * properly.
     *
     * @param bool $areWeInCallbackChain Set the status of the callback chain execution
     * @see CallbackChainHelperTrait::terminateCallbackChain() Dependent method
     * @see CallbackChainHelperTrait::isCallbackChainToBeTerminated() Dependent method
     */
    protected function inCallbackChain($areWeInCallbackChain)
    {
        $this->inCallbackChain = (bool)$areWeInCallbackChain;
    }

    /**
     * Get the status of the callback chain termination
     *
     * This method can be used to determine if the callback chain is to be terminated prematurely.
     *
     * @return bool
     */
    protected function isCallbackChainToBeTerminated()
    {
        return $this->terminateCallbackChain;
    }

    /**
     * Mark callback chain terminated
     *
     * This method should be called after the callback chain was terminated, either by breaking out of it prematurely
     * or after finishing all callback handlers.
     */
    protected function markCallbackChainTerminated()
    {
        $this->inCallbackChain(false);
        $this->terminateCallbackChain = false;
    }
}
