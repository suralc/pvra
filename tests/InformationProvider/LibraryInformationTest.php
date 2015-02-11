<?php
namespace Pvra\tests\InformationProvider;


use Pvra\InformationProvider\LibraryInformation;

class LibraryInformationTest extends \PHPUnit_Framework_TestCase
{
    public function testCreateWithDefaultsReturn()
    {
        $info = LibraryInformation::createWithDefaults();
        $this->assertInstanceOf('Pvra\\InformationProvider\\LibraryInformation', $info);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage does not exist or is not readable
     */
    public function testCreateFromFileError()
    {
        LibraryInformation::createFromFile(__DIR__ . '/non-existing-file.php');
    }

    public function testToArrayFromEmpty()
    {
        $info = new LibraryInformation();
        $this->assertEquals([
            'additions' => [
                'class' => [],
                'function' => [],
                'constant' => [],
            ],
            'removals' => [
                'class' => [],
                'function' => [],
                'constant' => [],
            ],
            'deprecations' => [
                'class' => [],
                'function' => [],
                'constant' => [],
            ]
        ], $info->toArray());
    }

    public function testToArrayFromNonEmpty()
    {
        $in = [
            'additions' => [
                'class' => [
                    'Home' => '7.6.5'
                ],
                'function' => [],
                'constant' => [],
            ],
            'removals' => [
                'class' => [],
                'function' => [],
                'constant' => [],
            ],
            'deprecations' => [
                'class' => [],
                'function' => [],
                'constant' => [
                    'T_HANDLE' => '083'
                ],
            ]
        ];
        $info = new LibraryInformation($in);
        $this->assertEquals($in, $info->toArray());
    }

    public function testMergeInformation()
    {
        $base = new LibraryInformation();
        $new = new LibraryInformation(
            [
                'additions' => [
                    'constant' => [
                        'abc' => '4.5.6'
                    ]
                ]
            ]
        );
        $base->mergeWith($new);
        $this->assertArrayHasKey('abc', $base->toArray()['additions']['constant']);
        $this->assertSame('4.5.6', $base->toArray()['additions']['constant']['abc']);
        // empty array should not override existing value
        $new->mergeWith(new LibraryInformation(['additions' => ['constant' => []]]));
        $this->assertArrayHasKey('abc', $new->toArray()['additions']['constant']);
        $this->assertSame('4.5.6', $new->toArray()['additions']['constant']['abc']);
        // both with data
        $base = new LibraryInformation([
            'additions' => [
                'function' => [
                    'delta' => '1.2.3',
                    'beta' => '4.5.6'
                ],
                'class' => [
                    'Epsilon' => '7.8.9'
                ]
            ],
            'deprecations' => [
                'function' => [
                    'delta' => '5.6.7'
                ]
            ]
        ]);
        $new = new LibraryInformation([
            'additions' => [
                'function' => [
                    'delta' => '2.0.0',
                    'alpha' => '5.6.6',
                ],
                'class' => [],
                'constant' => [
                    'a' => '2.4.5'
                ]
            ],
            'deprecations' => []
        ]);
        $base2 = clone $base;
        $base->mergeWith($new);
        $this->assertEquals([
            'additions' => [
                'function' => [
                    'delta' => '2.0.0',
                    'beta' => '4.5.6',
                    'alpha' => '5.6.6',
                ],
                'class' => [
                    'Epsilon' => '7.8.9',
                ],
                'constant' => [
                    'a' => '2.4.5'
                ]
            ],
            'deprecations' => [
                'function' => [
                    'delta' => '5.6.7'
                ],
                'class' => [],
                'constant' => [],
            ],
            'removals' => [
                'function' => [],
                'class' => [],
                'constant' => [],
            ],
        ], $ar = $base->toArray());
        $this->assertEquals($base2->toArray(), $base2->mergeWith($base2)->toArray());
        $base2->mergeWith($base2)->mergeWith($new);
        $this->assertEquals($ar, $base2->toArray());
    }

    public function testGetFunctionInfo()
    {
        $info = new LibraryInformation([
            'additions' => [
                'function' => [
                    'abc' => '4.5.6',
                    'def' => '5.6.7'
                ]
            ],
            'removals' => [
                'function' => [
                    'abc' => '6.0.0',
                ]
            ],
            'deprecations' => [
                'function' => [
                    'abc' => '5.0.0',
                    'depr' => '1.2.3',
                ]
            ]
        ]);
        $this->assertEquals([
            'addition' => '4.5.6',
            'removal' => '6.0.0',
            'deprecation' => '5.0.0',
        ], $info->getFunctionInfo('abc'));
        $this->assertEquals([
            'addition' => '5.6.7',
            'removal' => null,
            'deprecation' => null,
        ], $info->getFunctionInfo('def'));
        $this->assertEquals([
            'addition' => null,
            'removal' => null,
            'deprecation' => '1.2.3'
        ], $info->getFunctionInfo('\\depr'));
    }

    public function testGetClassInfo()
    {
        $info = new LibraryInformation([
            'additions' => [
                'class' => [
                    'abc' => '4.5.6',
                    'def' => '5.6.7'
                ]
            ],
            'removals' => [
                'class' => [
                    'abc' => '6.0.0',
                ]
            ],
            'deprecations' => [
                'class' => [
                    'abc' => '5.0.0',
                    'depr' => '1.2.3',
                ]
            ]
        ]);
        $this->assertEquals([
            'addition' => '4.5.6',
            'removal' => '6.0.0',
            'deprecation' => '5.0.0',
        ], $info->getClassInfo('abc'));
        $this->assertEquals([
            'addition' => '5.6.7',
            'removal' => null,
            'deprecation' => null,
        ], $info->getClassInfo('def'));
        $this->assertEquals([
            'addition' => null,
            'removal' => null,
            'deprecation' => '1.2.3'
        ], $info->getClassInfo('\\depr'));
    }

    public function testGetConstantInfo()
    {
        $info = new LibraryInformation([
            'additions' => [
                'constant' => [
                    'abc' => '4.5.6',
                    'def' => '5.6.7'
                ]
            ],
            'removals' => [
                'constant' => [
                    'abc' => '6.0.0',
                ]
            ],
            'deprecations' => [
                'constant' => [
                    'abc' => '5.0.0',
                    'depr' => '1.2.3',
                ]
            ]
        ]);
        $this->assertEquals([
            'addition' => '4.5.6',
            'removal' => '6.0.0',
            'deprecation' => '5.0.0',
        ], $info->getConstantInfo('abc'));
        $this->assertEquals([
            'addition' => '5.6.7',
            'removal' => null,
            'deprecation' => null,
        ], $info->getConstantInfo('def'));
        $this->assertEquals([
            'addition' => null,
            'removal' => null,
            'deprecation' => '1.2.3'
        ], $info->getConstantInfo('\\depr'));
    }
}
