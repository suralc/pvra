<?php

namespace Pvra\tests\Result;


use Pvra\Result\Collection;
use Pvra\Result\CollectionWriter;

class CollectionWriterTest extends \PHPUnit_Framework_TestCase
{
    protected $tmpFilePath;

    protected function setUp()
    {
        parent::setUp();
        $this->tmpFilePath = TEST_FILE_ROOT . '../testTmp/out.tmp';
    }

    protected function tearDown()
    {
        parent::tearDown();
        if (file_exists($this->tmpFilePath)) {
            unlink($this->tmpFilePath);
        }
    }

    public function testThatFileIsWrittenAndContainsContentReturnedByFormatter()
    {
        $this->assertFileNotExists($this->tmpFilePath);
        $writer = new CollectionWriter(new Collection());
        $formatterMock = $this->getMock('\\Pvra\\Result\\ResultFormatter\\ResultFormatter');
        $formatterMock->expects($this->once())->method('makePrintable')->willReturn('hello world2');
        $writer->write($this->tmpFilePath, $formatterMock, true);
        $this->assertFileExists($this->tmpFilePath);
        $this->assertSame('hello world2', file_get_contents($this->tmpFilePath));
    }

    public function testThatNoExceptionIsThrownOnExistingFileAndOverrideParameterFlagSet()
    {
        touch($this->tmpFilePath);
        $this->assertFileExists($this->tmpFilePath);
        $writer = new CollectionWriter(new Collection());
        $formatterMock = $this->getMock('\\Pvra\\Result\\ResultFormatter\\ResultFormatter');
        $formatterMock->expects($this->once())->method('makePrintable')->willReturn('hello world');
        $writer->write($this->tmpFilePath, $formatterMock, true);
        $this->assertFileExists($this->tmpFilePath);
        $this->assertSame('hello world', file_get_contents($this->tmpFilePath));
    }

    /**
     * @expectedException \Pvra\Result\Exceptions\ResultFileWriterException
     */
    public function testThatExceptionIsThrownOnExistingFileAndFormatIsNeverCalled()
    {
        touch($this->tmpFilePath);
        $this->assertFileExists($this->tmpFilePath);
        $writer = new CollectionWriter(new Collection());
        $formatterMock = $this->getMock('\\Pvra\\Result\\ResultFormatter\\ResultFormatter');
        $formatterMock->expects($this->never())->method('makePrintable');
        $writer->write($this->tmpFilePath, $formatterMock);
    }

    public function testBasicWriteToStreamFunctionality()
    {
        $str = fopen('php://temp', 'rw+');
        $writer = new CollectionWriter(new Collection());
        $formatterMock = $this->getMock('\\Pvra\\Result\\ResultFormatter\\ResultFormatter');
        $formatterMock->expects($this->once())->method('makePrintable')->willReturn('hello stream');
        $writer->writeToStream($str, $formatterMock);
        rewind($str);
        $this->assertSame('hello stream', stream_get_contents($str));
    }

    public function testThatWriteToStreamDoesNotOverrideContent()
    {
        $str = fopen('php://temp', 'rw+');
        fwrite($str, 'content');
        $writer = new CollectionWriter(new Collection());
        $formatterMock = $this->getMock('\\Pvra\\Result\\ResultFormatter\\ResultFormatter');
        $formatterMock->expects($this->once())->method('makePrintable')->willReturn(' is awesome');
        $writer->writeToStream($str, $formatterMock);
        rewind($str);
        $this->assertSame('content is awesome', stream_get_contents($str));
    }

    public function testThatExceptionsAreThrownOnWronglyTypedArgumentToWriteToStream()
    {
        $writer = new CollectionWriter(new Collection());
        $formatterMock = $this->getMock('\\Pvra\\Result\\ResultFormatter\\ResultFormatter');
        $formatterMock->expects($this->never())->method('makePrintable');
        $arguments = [
            1,
            false,
            'string',
            ['an array'],
            new \stdClass(),
        ];
        foreach ($arguments as $argument) {
            try {
                $writer->writeToStream($argument, $formatterMock);
                $this->fail('Expected exception not found and mock expectation failed.');
            } catch (\InvalidArgumentException $e) {
                $this->assertStringStartsWith('Parameter 1 of method CollectionWriter::writeToStream',
                    $e->getMessage());
            }
        }
    }
}
