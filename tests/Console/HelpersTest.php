<?php

namespace Console;


class HelpersTest extends \PHPUnit_Framework_TestCase
{
    public function makeRelativeTestData()
    {
        return [
            // [ from, to, expected ]
            [__DIR__ . '/Commands', __DIR__ . '/', '../'],
            [TEST_FILE_ROOT, TEST_FILE_ROOT . '7.0/all70.php', './7.0/all70.php'],
            [TEST_FILE_ROOT . '/////messageSource.xml', TEST_FILE_ROOT . '/////7.0////all70.php', './7.0/all70.php'],
            [TEST_FILE_ROOT . 'messageArray.php', TEST_FILE_ROOT . '5.3/all53.php', './5.3/all53.php']
        ];
    }

    /**
     * @dataProvider makeRelativeTestData
     */
    public function testMakeRelative($from, $to, $expected)
    {
        $this->assertSame($expected, \Pvra\Console\makeRelativePath($from, $to));
    }

    public function testGetArrayExtremeMax() {
        $data = [1,2,3,4,5,6,7];
        $this->assertSame(7,\Pvra\Console\get_array_max_value($data, $this->getIntComparator()));
        $data = array_reverse($data, false);
        $this->assertSame(7,\Pvra\Console\get_array_max_value($data, $this->getIntComparator()));
        shuffle($data);
        $this->assertSame(7,\Pvra\Console\get_array_max_value($data, $this->getIntComparator()));
        $data = [0,0,0,0,7,7,7,7,7,7,0,0,0,0,0];
        $this->assertSame(7,\Pvra\Console\get_array_max_value($data, $this->getIntComparator()));
    }

    public function testGetArrayExtremeMin() {
        $data = [1,2,3,4,5,6,7];
        $this->assertSame(1,\Pvra\Console\get_array_min_value($data, $this->getIntComparator()));
        $data = array_reverse($data, false);
        $this->assertSame(1,\Pvra\Console\get_array_min_value($data, $this->getIntComparator()));
        shuffle($data);
        $this->assertSame(1,\Pvra\Console\get_array_min_value($data, $this->getIntComparator()));
        $data = [0,0,0,0,7,7,7,7,7,7,0,0,0,0,0];
        $this->assertSame(0,\Pvra\Console\get_array_min_value($data, $this->getIntComparator()));
    }

    private function getIntComparator() {
        return static function($a, $b){
            return $a < $b ? -1 : ($a > $b ? 1: 0);
        };
    }
}
