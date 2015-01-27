<?php

namespace Pvra\tests\Console\Services;

use Mockery as m;
use Pvra\Console\Services\FileFinderBuilder;
use Pvra\tests\testFiles\ExtendedFinder;

require_once TEST_FILE_ROOT . 'ExtendedFinder.php';

class FileFinderBuilderTest extends \PHPUnit_Framework_TestCase
{
    protected function tearDown()
    {
        m::close();
    }

    public function testCustomFinderIsUsed()
    {
        $this->assertInstanceOf('\Pvra\tests\testFiles\ExtendedFinder',
            (new FileFinderBuilder(__DIR__, new ExtendedFinder()))->getFinder());
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage is not a valid directory
     */
    public function testNonExistingDirOnConstructError()
    {
        $f = new FileFinderBuilder('non-exsisting-dir');
    }

    public function testDefaultValuesAreSetOnConstruct()
    {
        $finderMock = $this->getDefaultFinderMock();
        $finderMock->shouldReceive('in')->once()->with(TEST_FILE_ROOT)->andReturnSelf();
        $finderMock->shouldReceive('ignoreDotFiles')->once()->with(true)->andReturnSelf();
        $finderMock->shouldReceive('ignoreVCS')->once()->with(true)->andReturnSelf();
        new FileFinderBuilder(TEST_FILE_ROOT, $finderMock);
    }

    public function testIncludeDotFiles()
    {
        $finderMock = $this->getDefaultFinderMock();
        $finderMock->shouldReceive('ignoreDotFiles')->twice()->with(true)->andReturnSelf();
        $finderMock->shouldReceive('ignoreDotFiles')->twice()->with(false)->andReturnSelf();
        (new FileFinderBuilder(TEST_FILE_ROOT,
            $finderMock))->includeDotFiles(false)->includeDotFiles(true)->includeDotFiles();
    }

    public function testIsRecursive()
    {
        $finderMock = $this->getDefaultFinderMock();
        $finderMock->shouldReceive('depth')->twice()->with('>= 0')->andReturnSelf();
        $finderMock->shouldReceive('depth')->once()->with(0)->andReturnSelf();
        (new FileFinderBuilder(TEST_FILE_ROOT, $finderMock))->isRecursive()->isRecursive(true)->isRecursive(false);
    }

    public function testSortBy()
    {
        $finderMock = $this->getDefaultFinderMock();
        $finderMock->shouldReceive('sortByName')->twice()->andReturnSelf();
        (new FileFinderBuilder(TEST_FILE_ROOT, $finderMock))->sortBy(FileFinderBuilder::SORT_BY_NAME)->sortBy('name');
        $finderMock = $this->getDefaultFinderMock();
        $finderMock->shouldReceive('sortByChangedTime')->twice()->andReturnSelf();
        (new FileFinderBuilder(TEST_FILE_ROOT, $finderMock))->sortBy(FileFinderBuilder::SORT_BY_CHANGED_TIME)->sortBy('ctime');
        $finderMock = $this->getDefaultFinderMock();
        $finderMock->shouldReceive('sortByAccessedTime')->twice()->andReturnSelf();
        (new FileFinderBuilder(TEST_FILE_ROOT, $finderMock))->sortBy(FileFinderBuilder::SORT_BY_ACCESSED_TIME)->sortBy('atime');
        $finderMock = $this->getDefaultFinderMock();
        $finderMock->shouldReceive('sortByType')->twice()->andReturnSelf();
        (new FileFinderBuilder(TEST_FILE_ROOT, $finderMock))->sortBy(FileFinderBuilder::SORT_BY_TYPE)->sortBy('type');
        $finderMock = $this->getDefaultFinderMock();
        $finderMock->shouldReceive('sortByModifiedTime')->twice()->andReturnSelf();
        (new FileFinderBuilder(TEST_FILE_ROOT, $finderMock))->sortBy(FileFinderBuilder::SORT_BY_MODIFIED_TIME)->sortBy('mtime');
    }

    public function testSortByWithCallable()
    {
        $callable = function() { return 0; };
        $finderMock = $this->getDefaultFinderMock();
        $finderMock->shouldReceive('sort')->once()->with($callable)->andReturnSelf();
        (new FileFinderBuilder(TEST_FILE_ROOT, $finderMock))->sortBy($callable);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage is not a supported argument for sorting.
     */
    public function testSortByError()
    {
        (new FileFinderBuilder(TEST_FILE_ROOT))->sortBy('non-existing');
    }

    public function testGetIterator()
    {
        $finderBuilder = new FileFinderBuilder(TEST_FILE_ROOT);
        $this->assertInstanceOf('\Iterator', $finderBuilder->getIterator());
        $this->assertInstanceOf('\IteratorAggregate', $finderBuilder);
    }

    public function testGetFinder()
    {
        $finder = new ExtendedFinder();
        $finderBuilder = new FileFinderBuilder(TEST_FILE_ROOT, $finder);
        $this->assertSame($finder, $finderBuilder->getFinder());
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The filter "*.php" is not a valid filter.
     */
    public function testWithFiltersInvalidFilter()
    {
        $finderBuilder = new FileFinderBuilder(TEST_FILE_ROOT);
        $finderBuilder->withFilters(['*.php']);
    }

    public function testWithFilters()
    {
        $finder = $this->getDefaultFinderMock();
        $finder->shouldReceive('exclude')->once()->with('exclude-value')->andReturnSelf();
        $finder->shouldReceive('name')->once()->with('name-value')->andReturnSelf();
        $finder->shouldReceive('notName')->once()->with('notName-value')->andReturnSelf();
        $finder->shouldReceive('path')->once()->with('path-value')->andReturnSelf();
        $finder->shouldReceive('size')->once()->with('size1-value')->andReturnSelf();
        $finder->shouldReceive('size')->once()->with('size2-value')->andReturnSelf();
        $finderBuilder = new FileFinderBuilder(TEST_FILE_ROOT, $finder);
        $this->assertInstanceOf('\Pvra\Console\Services\FileFinderBuilder', $finderBuilder->withFilters([
            'size:size2-value',
            'name:name-value',
            ' exclude :exclude-value',
            'notName:notName-value',
            'path:path-value',
            'size:size1-value',
        ]));
    }

    /**
     * @return \Mockery\MockInterface|\Symfony\Component\Finder\Finder
     */
    private function getDefaultFinderMock()
    {
        return m::mock('\Symfony\Component\Finder\Finder')->makePartial();
    }
}
