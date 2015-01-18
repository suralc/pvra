<?php

namespace Pvra\tests\Result;


use Pvra\Result\MessageLocator;

class MessageLocatorTest extends \PHPUnit_Framework_TestCase
{
    public function testArrayAccessOffsetExists()
    {
        $l = new MessageLocator();
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
        $l = new MessageLocator();
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
        $l = new MessageLocator();
        unset($l['abc']);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testArrayAccessOffsetSetException()
    {
        $l = new MessageLocator();
        $l[12] = 'abc';
    }

    public function testArrayAccessOffsetSet()
    {
        $l = new MessageLocator(false);
        $this->assertNull($l->getMessage(12));
        $l[] = function ($id) {
            return 'my_msg';
        };
        $this->assertSame('my_msg', $l->getMessage(12));
    }


    public function testMissingMethodHandlers()
    {
        $l = new MessageLocator();
        $this->assertFalse($l->messageExists('my_msg'));
        $l->addMissingMessageHandler(function ($id) {
            if ($id === 1) {
                return 'a msg';
            }

            return false;
        });
        $this->assertSame('a msg', $l->getMessage(1));
        $l->addMissingMessageHandler(function ($id, MessageLocator $locator) {
            if ($id === 1) {
                $locator->terminateCallbackChain();
                return 'new';
            }

            return false;
        }, MessageLocator::CALLBACK_POSITION_PREPEND);
        $this->assertSame('new', $l->getMessage(1));
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage A callback chain can only be terminated from within a callback.
     */
    public function testExceptionOnUnexpectedCallbackChainTerminated()
    {
        $l = new MessageLocator();
        $l->terminateCallbackChain();
    }

    public function testMessageExists()
    {
        $locator = new MessageLocator();

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
        $locator->getMessage('a string'); // fetch the message
        $this->assertTrue($locator->messageExists('a string')); // make sure it still exists
    }

    public function testFromArray()
    {
        $locator = MessageLocator::fromArray([
            'a' => 'a string',
            'b' => 'b string',
        ]);

        $this->assertTrue($locator->messageExists('a'));
        $this->assertTrue($locator->messageExists('a'));
        $this->assertFalse($locator->messageExists('c'));
    }

    public function testFromPhpFile()
    {
        $locator = MessageLocator::fromPhpFile(TEST_FILE_ROOT . 'messageArray.php');

        $this->assertTrue($locator->messageExists(1));
        $this->assertTrue($locator->messageExists('2'));
        $this->assertFalse($locator->messageExists('item'));
    }

    public function testFromJsonFile()
    {
        $locator = MessageLocator::fromJsonFile(TEST_FILE_ROOT . 'msg_file_cmd.json');

        $this->assertTrue($locator->messageExists(13));
        $this->assertTrue($locator->messageExists('13'));
        $this->assertFalse($locator->messageExists(14));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testFromJsonFileInvalidArgumentExceptionOnMissingFile()
    {
        $locator = MessageLocator::fromJsonFile('non-existing.json');
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testFromJsonFileInvalidArgumentExceptionOnInvalidJsonFile()
    {
        $locator = MessageLocator::fromJsonFile(TEST_FILE_ROOT . 'invalid_formed_json.json');
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testFromPhpFileNotFound()
    {
        $locator = MessageLocator::fromPhpFile('non-existant');
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Only valid non-empty offset types are acceptable as message ids.
     */
    public function testMessageSearchTerminationException()
    {
        $locator = MessageLocator::fromArray([]);
        $this->assertFalse($locator->messageExists(new \stdClass()));
    }

    public function testCallbackChainTermination()
    {
        $locator = new MessageLocator();

        $locator->addMissingMessageHandler(function ($id, MessageLocator $locator) {
            $locator->terminateCallbackChain();
            return false;
        });

        $locator->addMissingMessageHandler(function () {
            $this->fail('This handler should not have been called.');
        });

        $locator->addMessageSearcher(function ($id, MessageLocator $locator) {
            $locator->terminateCallbackChain();
        });
        $locator->addMessageSearcher(function () {
            return 'my message';
        });
        $this->assertFalse($locator->messageExists('some'));
        $this->assertNull($locator->getMessage('some'));
    }
}
