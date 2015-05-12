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
}
