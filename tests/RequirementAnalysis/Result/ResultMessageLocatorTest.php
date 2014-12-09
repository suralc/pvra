<?php

namespace Pvra\tests\RequirementAnalysis\Result;


use Pvra\RequirementAnalysis\Result\ResultMessageLocator;

class ResultMessageLocatorTest extends \PHPUnit_Framework_TestCase
{
    public function testDefaultSuffixAppend()
    {
        $locator = new ResultMessageLocator(true);
        $locator->addMessageSearcher($f = function ($id) {
            return 'my_msg';
        });
        $this->assertTrue($locator->messageExists('my_id'));
        $this->assertNotEquals($f('my_id'), $locator->getMessage('my_id'));
        $this->assertStringEndsNotWith('msg', $locator->getMessage('my_id'));
    }

    public function testArrayAccessOffsetExists()
    {
        $l = new ResultMessageLocator(false);
        $this->assertFalse(isset($l['abc']));
        $l->addMessageSearcher(function ($id) {
            if ($id === 12) {
                return 'a msg';
            }
            return false;
        });
        $this->assertTrue(isset($l[12]));
        $this->assertFalse(isset($l[15]));
    }

    public function testArrayAccessOffsetGet()
    {
        $l = new ResultMessageLocator(false);
        $l->addMessageSearcher(function ($id) {
            if ($id === 12) {
                return 'a msg';
            }
            return false;
        });
        $l->addMissingMessageHandler(function ($id) {
            return 'missing';
        });
        $this->assertSame('a msg', $l[12]);
        $this->assertSame('missing', $l['abc']);
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage This operation is  unsupported.
     */
    public function testArrayAccessOffsetUnset()
    {
        $l = new ResultMessageLocator(false);
        unset($l['abc']);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testArrayAccessOffsetSetException()
    {
        $l = new ResultMessageLocator();
        $l[12] = 'abc';
    }

    public function testArrayAccessOffsetSet()
    {
        $l = new ResultMessageLocator(false);
        $this->assertNull($l->getMessage(12));
        $l[] = function ($id) {
            return 'my_msg';
        };
        $this->assertSame('my_msg', $l->getMessage(12));
    }

    public function testAddTransformerPrepend()
    {
        $locator = new ResultMessageLocator(false);
        $locator->addMessageSearcher(function () {
            return 'msg';
        });
        $locator->addTransformer(function ($id, $format) {
            return $format . 'trans1';
        });
        $this->assertStringEndsWith('trans1', $locator->getMessage('my_id'));
        $locator->addTransformer(function ($id, $format) {
            return $format . 'trans2';
        }, ResultMessageLocator::CALLBACK_POSITION_PREPEND);
        $this->assertStringEndsWith('trans2trans1', $locator->getMessage('my_id'));
    }

    public function testMissingMethodHandlers()
    {
        $l = new ResultMessageLocator(false);
        $this->assertFalse($l->messageExists('my_msg'));
        $l->addMissingMessageHandler(function ($id) {
            if ($id === 1) {
                return 'a msg';
            }

            return false;
        });
        $this->assertSame('a msg', $l->getMessage(1));
        $l->addMissingMessageHandler(function ($id, ResultMessageLocator $locator) {
            if ($id === 1) {
                $locator->terminateCallbackChain();
                return 'new';
            }

            return false;
        }, ResultMessageLocator::CALLBACK_POSITION_PREPEND);
        $this->assertSame('new', $l->getMessage(1));
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage A callback chain can only be terminated from within a callback.
     */
    public function testExceptionOnUnexpectedCallbackChainTerminated()
    {
        $l = new ResultMessageLocator();
        $l->terminateCallbackChain();
    }

    public function testMessageExists()
    {
        $locator = new ResultMessageLocator(false);

        $this->assertFalse($locator->messageExists(298));
        $this->assertFalse($locator->messageExists('243'));
        $locator->addMessageSearcher(function ($msgId) {
            if ($msgId === 12) {
                return 'Hello from callback';
            } else {
                return false;
            }
        });
        $this->assertTrue($locator->messageExists(12));
        $this->assertInternalType('string', $locator->getMessage(12));
        $this->assertTrue($locator->messageExists(12)); // go into first if branch
        $this->assertFalse($locator->messageExists('some other'));
        $locator->addMessageSearcher(function ($msgId) {
            if ($msgId === 'a string') {
                return 'callback2';
            }

            return false;
        });
        $this->assertTrue($locator->messageExists(12));
        $this->assertTrue($locator->messageExists('a string'));
    }

    public function testFromArray()
    {
        $locator = ResultMessageLocator::fromArray([
            'a' => 'a string',
            'b' => 'b string',
        ]);

        $this->assertTrue($locator->messageExists('a'));
        $this->assertTrue($locator->messageExists('a'));
        $this->assertFalse($locator->messageExists('c'));
    }

    public function testFromPhpFile()
    {
        $locator = ResultMessageLocator::fromPhpFile(TEST_FILE_ROOT . 'messageArray.php');

        $this->assertTrue($locator->messageExists(1));
        $this->assertTrue($locator->messageExists('2'));
        $this->assertFalse($locator->messageExists('item'));
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Only valid non-empty offset types are acceptable as message ids.
     */
    public function testMessageSearchTerminationException()
    {
        $locator = ResultMessageLocator::fromArray([]);
        $this->assertFalse($locator->messageExists(new \stdClass()));
    }

    public function testCallbackChainTermination()
    {
        $locator = new ResultMessageLocator(false);

        $locator->addMessageSearcher(function ($id, ResultMessageLocator $locator) {
            $locator->terminateCallbackChain();
        });
        $locator->addMessageSearcher(function () {
            return 'my message';
        });
        $this->assertFalse($locator->messageExists('some'));
        $this->assertNull($locator->getMessage('some'));

        $locator = new ResultMessageLocator(false);
        $locator->addTransformer(function ($id, $format, ResultMessageLocator $locator) {
            $locator->terminateCallbackChain();
            return $format . $id . 'end';
        });
        $locator->addTransformer(function () {
            return 'begin';
        });
        $locator->addMessageSearcher(function () {
            return 'MyString';
        });
        $this->assertTrue($locator->messageExists('msg'));
        $this->assertStringEndsNotWith('begin', $locator->getMessage('msg'));
        $this->assertStringEndsWith('end', $locator->getMessage('msg'));
        $this->assertTrue(substr_count($locator->getMessage('msg'), 'end') === 1);
    }
}
