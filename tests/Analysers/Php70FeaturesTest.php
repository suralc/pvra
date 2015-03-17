<?php
/**
 * Php70FeaturesTest.php
 *
 * MIT LICENSE
 *
 * LICENSE: This source file is subject to the MIT license.
 * A copy of the licenses text was distributed alongside this
 * file (usually the repository or package root). The text can also
 * be obtained on one of the following sources:
 * * http://opensource.org/licenses/MIT
 * * https://github.com/suralc/pvra/blob/master/LICENSE
 *
 * @author     suralc <thesurwaveing@gmail.com>
 * @license    http://opensource.org/licenses/MIT  MIT
 */

namespace Pvra\tests\Analysers;


use Pvra\Analysers\Php70Features;
use Pvra\Result\Reason as R;
use Pvra\tests\BaseNodeWalkerTestCase;

class Php70FeaturesTest extends BaseNodeWalkerTestCase
{
    protected $classToTest = 'Pvra\Analysers\Php70Features';
    protected $expandNames = true;

    public function testReturnType()
    {
        $expected = [
            [4, R::RETURN_TYPE],
            [5, R::RETURN_TYPE],
            [6, R::RETURN_TYPE],
            [14, R::RETURN_TYPE],
            [17, R::RETURN_TYPE],
            [18, R::RETURN_TYPE],
            [25, R::RETURN_TYPE],
        ];

        $this->runTestsAgainstExpectation($expected, '7.0/return_type', '7.0.0');
    }

    public function testConstructorDeprecation()
    {
        $expected = [
            [6, R::PHP4_CONSTRUCTOR],
            [13, R::PHP4_CONSTRUCTOR],
            [20, R::PHP4_CONSTRUCTOR],
        ];

        $this->runTestsAgainstExpectation($expected, '7.0/php4_ctor', '-7.0.0', Php70Features::MODE_ALL);
    }

    /**
     * @dataProvider nonDeprecationFlagProvider
     */
    public function testPhp4ConstructorsAreNotMarkedWithoutDeprecationFlag($mode)
    {
        $expected = [];

        $this->runTestsAgainstExpectation($expected, '7.0/php4_ctor', null, $mode);
    }

    public function nonDeprecationFlagProvider()
    {
        return [
            [Php70Features::MODE_ADDITION],
            [Php70Features::MODE_REMOVAL],
            [Php70Features::MODE_ALL & ~Php70Features::MODE_DEPRECATION],
            [Php70Features::MODE_REMOVAL | Php70Features::MODE_ADDITION]
        ];
    }
}
