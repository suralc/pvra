<?php

namespace Pvra\tests\Result\ResultFormatter;


use Pvra\Result\Exceptions\ResultFileWriterException;
use Pvra\Result\ResultFormatter\Json;

class JsonTest extends \PHPUnit_Framework_TestCase
{
    public function testThatExceptionIsThrownOnInvalidJsonGeneration()
    {
        $formatter = new Json();
        $collection = $this->getMock('Pvra\\Result\\Collection');
        $collection->expects($this->once())->method('jsonSerialize')->willReturn(pack("H*" ,'c32e'));
        try {
            $formatter->makePrintable($collection);
            $this->fail('Expected exception was not thrown.');
        } catch (ResultFileWriterException $e) {
            if (PHP_VERSION_ID >= 50500) {
                $this->assertSame('Json Encoding failed with error: 5: Malformed UTF-8 characters, possibly incorrectly encoded', $e->getMessage());
            } else {
                $this->assertSame('Json Encoding failed with error: 5', $e->getMessage());
            }
        }
    }

    public function testThatOptionsArePassed()
    {
        $j1 = new Json(['options' => JSON_PRETTY_PRINT]);
        $c1 = $this->getMock('Pvra\\Result\\Collection');
        $c1->expects($this->once())->method('jsonSerialize')->willReturn([[['hello' => new \stdClass()]]]);
        $this->assertSame(json_encode([[['hello' => new \stdClass()]]], JSON_PRETTY_PRINT), $j1->makePrintable($c1));
    }

    /**
     * @expectedException \Pvra\Result\Exceptions\ResultFileWriterException
     * @expectedExceptionMessage Json Encoding failed with error: 1: Maximum stack depth exceeded
     */
    public function testThatDepthIsPassed()
    {
        if(PHP_VERSION_ID < 50500) {
            $this->markTestSkipped('The depth parameter requires php >= 5.5');
        }
        $j1 = new Json(['depth' => 2]);
        $c1 = $this->getMock('Pvra\\Result\\Collection');
        $c1->expects($this->once())->method('jsonSerialize')->willReturn(['a' => [[[[[[[[[[[[['hello']]]]]]]]]]]]]]);
        $j1->makePrintable($c1);
    }

    public function testThatSettingDepthOptionDoesNotTriggerAnErrorAndIsIgnoredOnPre55()
    {
        if(PHP_VERSION_ID >= 50500) {
            $this->markTestSkipped('The depth parameter is always valid on php >= 5.5');
        }
        $j1 = new Json(['depth' => 2]);
        $c1 = $this->getMock('Pvra\\Result\\Collection');
        $c1->expects($this->once())->method('jsonSerialize')->willReturn(['a' => [[[[[[[[[[[[['hello']]]]]]]]]]]]]]);
        $this->assertSame(json_encode(['a' => [[[[[[[[[[[[['hello']]]]]]]]]]]]]]), $j1->makePrintable($c1));
    }

    public function testThatPreviousJsonErrorDoesNotInterfereWithCurrentJsonGeneration()
    {
        $formatter = new Json();
        $collection = $this->getMock('Pvra\\Result\\Collection');
        $collection->expects($this->once())->method('jsonSerialize')->willReturn('working');
        json_encode(pack("H*" ,'c32e'));
        $this->assertSame('"working"', $formatter->makePrintable($collection));
    }
}
