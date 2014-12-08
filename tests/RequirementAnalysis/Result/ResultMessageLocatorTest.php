<?php

namespace Pvra\tests\RequirementAnalysis\Result;


use Pvra\RequirementAnalysis\Result\ResultMessageLocator;

class ResultMessageLocatorTest extends \PHPUnit_Framework_TestCase
{
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
        $locator->addTransformer(function($id, $format, ResultMessageLocator $locator) {
            $locator->terminateCallbackChain();
            return $format . $id . 'end';
        });
        $locator->addTransformer(function() {
            return 'begin';
        });
        $locator->addMessageSearcher(function() {
            return 'MyString';
        });
        $this->assertTrue($locator->messageExists('msg'));
        $this->assertStringEndsNotWith('begin', $locator->getMessage('msg'));
        $this->assertStringEndsWith('end', $locator->getMessage('msg'));
        $this->assertTrue(substr_count($locator->getMessage('msg'), 'end') === 1);
    }
}
