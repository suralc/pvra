<?php

namespace Pvra\tests\Result;


use Pvra\Result\MessageFormatter;
use Pvra\Result\MessageLocator;
use Pvra\Result\Reason;

class MessageFormatterTest extends \PHPUnit_Framework_TestCase
{
    public function testDefaultFormatter()
    {
        $f = new MessageFormatter($this->getDefaultLocator([1 => 'template']));
        $this->assertStringEndsWith(':line:', $f->getFormattedMessageFromId(1));
    }

    public function testDefaultLocatorCreation()
    {
        $f = new MessageFormatter();
        $this->assertInstanceOf('\Pvra\Result\MessageLocator', $f->getLocator());
        $this->assertInternalType('string', $f->getLocator()[ Reason::ARRAY_FUNCTION_DEREFERENCING ]);
        $this->assertTrue($f->getLocator()->messageExists(Reason::LIB_CLASS_ADDITION));
    }

    public function testLocatorCreationByArray()
    {
        $f = new MessageFormatter([12 => 'a msg'], false);
        $this->assertSame('a msg', $f->getFormattedMessageFromId(12));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testLocatorCreationFromInvalidArgument()
    {
        $f = new MessageFormatter(new \stdClass());
    }

    public function testDefaultExclusiveMissingMessageHandler()
    {
        $f = new MessageFormatter($this->getDefaultLocator(), false, true);
        $this->assertStringMatchesFormat('Message for id "%s" %s could not be found.',
            $f->getFormattedMessageFromId(12));
        $f->getLocator()->addMissingMessageHandler(function () {
            return 'abc';
        });
        $this->assertStringMatchesFormat('Message for id "%s" %s could not be found.',
            $m = $f->getFormattedMessageFromId(12));
        $this->assertTrue(strpos($m, 'TRAIT_MAGIC_CONST') !== false);
    }

    public function testFormatWithoutUserFormatters()
    {
        $f = new MessageFormatter($this->getDefaultLocator(), false);
        $this->assertSame('12', $f->format(':id:', ['id' => 12], false));
        $this->assertSame('ein kleiner Hase', $f->format(':a::space::little::space::h:',
            ['space' => ' ', 'a' => 'ein', 'little' => 'kleiner', 'h' => 'Hase']));
        $this->assertSame('15', $f->format(['id' => 15, 'template' => ':id:']));
    }

    public function testGetMessageTemplate()
    {
        $f = new MessageFormatter();
        $this->assertInternalType('string', $f->getMessageTemplate(Reason::ARRAY_FUNCTION_DEREFERENCING));
    }

    /**
     * @expectedException \Exception
     */
    public function testGetMessageTemplateException()
    {
        $f = new MessageFormatter($this->getDefaultLocator(), false, false, true);
        $f->getMessageTemplate(12);
    }

    public function testMessageForIdExists()
    {
        $f = new MessageFormatter($this->getDefaultLocator([12 => 'abc']));
        $this->assertFalse($f->messageForIdExists(15));
        $this->assertTrue($f->messageForIdExists(12));
    }

    public function testMessageFormatters()
    {
        $f = new MessageFormatter($this->getDefaultLocator([12 => 'abc']), false);
        $f->addMessageFormatter(function($id, $format) {
            return str_replace('a', 'd', $format);
        });
        $this->assertSame('dbc', $f->getFormattedMessageFromId(12));
        $f->addMessageFormatter(function($id, $format, MessageFormatter $f) {
            return str_replace('b', 'x', $format);
        }, MessageFormatter::CALLBACK_POSITION_PREPEND);
        $this->assertSame('dxc', $f->getFormattedMessageFromId(12));
        $f->addMessageFormatter(function($id, $format, MessageFormatter $f) {
            $f->terminateCallbackChain();
            return str_replace('a', 'geh', $format);
        }, MessageFormatter::CALLBACK_POSITION_PREPEND);
        $this->assertSame('gehbc', $f->getFormattedMessageFromId(12));
    }

    /**
     * @param array $data
     * @return static
     */
    private function getDefaultLocator(array $data = [])
    {
        return MessageLocator::fromArray($data);
    }
}
