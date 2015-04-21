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
        $collection->expects($this->once())->method('jsonSerialize')->willReturn($str = fopen('php://temp', 'r+'));
        try {
            $formatter->makePrintable($collection);
            $this->fail('Expected exception was not thrown.');
        } catch (ResultFileWriterException $e) {
            if (PHP_VERSION_ID >= 50500) {
                $this->assertSame('Json Encoding failed with error: 8: Type is not supported', $e->getMessage());
            } else {
                $this->assertSame('Json Encoding failed with error: 8', $e->getMessage());
            }
        }
        fclose($str);
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
        $j1 = new Json(['depth' => 2]);
        $c1 = $this->getMock('Pvra\\Result\\Collection');
        $c1->expects($this->once())->method('jsonSerialize')->willReturn(['a' => [[[[[[[[[[[[['hello']]]]]]]]]]]]]]);
        $j1->makePrintable($c1);
    }

    public function testThatPreviousJsonErrorDoesNotInterfereWithCurrentJsonGeneration()
    {
        $formatter = new Json();
        $collection = $this->getMock('Pvra\\Result\\Collection');
        $collection->expects($this->once())->method('jsonSerialize')->willReturn('working');
        json_encode($str = fopen('php://temp', 'r+'));
        $this->assertSame('"working"', $formatter->makePrintable($collection));
        fclose($str);
    }
}
