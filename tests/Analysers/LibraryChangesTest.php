<?php
namespace Pvra\tests\Analysers;


use Mockery as m;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\StaticPropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;
use Pvra\Analysers\LibraryChanges;
use Pvra\AnalysisResult;
use Pvra\InformationProvider\LibraryInformation;
use Pvra\Result\Reason;
use Pvra\StringAnalyser;
use Pvra\tests\BaseNodeWalkerTestCase;

class LibraryChangesTest extends BaseNodeWalkerTestCase
{
    protected $classToTest = 'Pvra\\Analysers\\LibraryChanges';
    protected $expandNames = true;

    public function testCreationWithInformation()
    {
        $result = new AnalysisResult();
        $analyserMock = m::mock('Pvra\\Analyser');
        $analyserMock->shouldReceive('getResult')->andReturn($result);
        $info = new LibraryInformation([
            'additions' => [
                'function' => [
                    'def' => '7.0.0'
                ]
            ]
        ]);
        $chg = new LibraryChanges([], $analyserMock, $info);
        $chg->enterNode(new FuncCall('def'));
        $this->assertSame('7.0.0', $result->getRequiredVersion());
    }

    public function testAddLibraryInformation()
    {
        /** @var \Mockery\MockInterface $lib1mock */
        $lib1mock = m::mock('Pvra\\InformationProvider\\LibraryInformation')->makePartial();
        $lib1mock->shouldReceive('mergeWith')->once()->andReturnSelf();
        $chg = (new LibraryChanges())->setLibraryInformation($lib1mock);
        $this->assertInstanceOf(get_class($chg), $chg->addLibraryInformation(new LibraryInformation()));
    }

    public function testGetLibraryWithDataPathSet()
    {
        $chg = new LibraryChanges(['libraryDataPath' => TEST_FILE_ROOT . 'simple_lib_data_source.php']);
        $this->assertInstanceOf('Pvra\\InformationProvider\\LibraryInformation', $chg->getLibraryInformation());
        $this->assertEquals(['addition' => '5.6.3', 'deprecation' => null, 'removal' => null],
            $chg->getLibraryInformation()->getFunctionInfo('substr'));
    }

    public function testEnsureNameNodeNameToString()
    {
        $result = new AnalysisResult();
        $analyserMock = m::mock('Pvra\\Analyser');
        $analyserMock->shouldReceive('getResult')->andReturn($result);
        $libInfo = new LibraryInformation(['additions' => ['function' => ['barBaz' => '7.0.0']]]);
        $chg = new LibraryChanges([], $analyserMock, $libInfo);
        $chg->enterNode(new FuncCall(new Name('barBaz')));
        $this->assertSame('7.0.0', $result->getRequiredVersion());
    }

    public function testMixedDetection()
    {
        $res = $this->runInstanceFromScratch('libraryAdditions', LibraryChanges::MODE_ALL);

        $expected = [
            [3, Reason::LIB_FUNCTION_ADDITION],
            [4, Reason::LIB_FUNCTION_ADDITION],
            [6, Reason::LIB_FUNCTION_ADDITION],
            [7, Reason::LIB_CLASS_ADDITION],
            [8, Reason::LIB_CLASS_ADDITION],
            [12, Reason::LIB_CLASS_ADDITION],
            [12, Reason::LIB_CLASS_ADDITION],
            [12, Reason::LIB_CLASS_ADDITION],
            [19, Reason::LIB_CLASS_ADDITION],
            [21, Reason::LIB_CLASS_ADDITION],
            [25, Reason::LIB_CLASS_ADDITION],
            [25, Reason::LIB_CLASS_ADDITION],
            [42, Reason::LIB_CLASS_ADDITION],
            [46, Reason::LIB_CLASS_ADDITION],
        ];

        $this->assertCount(count($expected) + /* 5.6 below the foreach */
            1, $res);

        foreach ($expected as $pos => $req) {
            $this->assertSame($req[0], $res->getRequirementInfo('5.4.0')[ $pos ]['line']);
            $this->assertSame($req[1], $res->getRequirementInfo('5.4.0')[ $pos ]['reason']);
        }

        $this->assertSame(4, $res->getRequirementInfo('5.6.0')[0]['line']);
        $this->assertSame(Reason::LIB_FUNCTION_ADDITION, $res->getRequirementInfo('5.6.0')[0]['reason']);
    }

    public function testPropertyOfNonObjectOnCountNamePartsInParameterTypeHint()
    {
        // this triggered a notice before the fix in 44f16c2bd9
        $result = $this->runInstanceFromScratch('libAdditionsPropOnNonObjInParamHint', LibraryChanges::MODE_ALL);
        $this->assertCount(0, $result);
    }

    public function testConstantDetectionWithModeOnlyAdditionsAndDeprecations()
    {
        $result = new AnalysisResult();
        $analyser = (new StringAnalyser(''))->setResultInstance($result);
        $chg = new LibraryChanges(['mode' => LibraryChanges::MODE_ADDITION | LibraryChanges::MODE_DEPRECATION],
            $analyser, $this->getDefaultLibraryInformation());
        $chg->enterNode(new ConstFetch(new Name('CONST_1')));
        $limits = $result->getLimits();
        $this->assertCount(1, $limits);
        $this->assertArrayHasKey('5.10.2', $limits);
        $this->assertCount(1, $limits['5.10.2']);
        $this->assertSame(-1, $limits['5.10.2'][0]['line']);
        $this->assertSame(Reason::LIB_CONSTANT_DEPRECATION, $limits['5.10.2'][0]['reason']);
        $req = $result->getRequirements();
        $this->assertCount(1, $req);
        $this->assertArrayHasKey('5.10.2', $req);
        $this->assertCount(1, $req['5.10.2']);
        $this->assertSame(-1, $req['5.10.2'][0]['line']);
        $this->assertSame(Reason::LIB_CONSTANT_ADDITION, $req['5.10.2'][0]['reason']);
    }

    public function testConstantDetectionWithModeAll()
    {
        $result = new AnalysisResult();
        $analyser = (new StringAnalyser(''))->setResultInstance($result);
        $chg = new LibraryChanges(['mode' => LibraryChanges::MODE_ALL],
            $analyser, $this->getDefaultLibraryInformation());
        $chg->enterNode(new ConstFetch(new Name('CONST_1')));
        $limits = $result->getLimits();
        $this->assertCount(1, $limits);
        $this->assertArrayHasKey('5.10.2', $limits);
        $this->assertCount(2, $limits['5.10.2']);
        $this->assertSame(-1, $limits['5.10.2'][0]['line']);
        $this->assertSame(-1, $limits['5.10.2'][1]['line']);
        $this->assertSame(Reason::LIB_CONSTANT_DEPRECATION, $limits['5.10.2'][0]['reason']);
        $this->assertSame(Reason::LIB_CONSTANT_REMOVAL, $limits['5.10.2'][1]['reason']);
        $req = $result->getRequirements();
        $this->assertCount(1, $req);
        $this->assertArrayHasKey('5.10.2', $req);
        $this->assertCount(1, $req['5.10.2']);
        $this->assertSame(-1, $req['5.10.2'][0]['line']);
        $this->assertSame(Reason::LIB_CONSTANT_ADDITION, $req['5.10.2'][0]['reason']);
    }

    public function testConstantDetectionWithModeNone()
    {
        $result = new AnalysisResult();
        $analyser = (new StringAnalyser(''))->setResultInstance($result);
        $chg = new LibraryChanges(['mode' => 0b0000],
            $analyser, $this->getDefaultLibraryInformation());
        $chg->enterNode(new ConstFetch(new Name('CONST_1')));
        $this->assertCount(0, $result->getLimits());
        $this->assertCount(0, $result->getRequirements());
    }

    public function testClassDetectionWithModeOnlyAdditionsAndDeprecations()
    {
        $result = new AnalysisResult();
        $analyser = (new StringAnalyser(''))->setResultInstance($result);
        $chg = new LibraryChanges(['mode' => LibraryChanges::MODE_ADDITION | LibraryChanges::MODE_DEPRECATION],
            $analyser, $this->getDefaultLibraryInformation());
        $chg->enterNode(new New_(new Name('Alpha')));
        $limits = $result->getLimits();
        $this->assertCount(1, $limits);
        $this->assertArrayHasKey('5.6.1', $limits);
        $this->assertCount(1, $limits['5.6.1']);
        $this->assertSame(-1, $limits['5.6.1'][0]['line']);
        $this->assertSame(Reason::LIB_CLASS_DEPRECATION, $limits['5.6.1'][0]['reason']);
        $req = $result->getRequirements();
        $this->assertCount(1, $req);
        $this->assertArrayHasKey('5.6.1', $req);
        $this->assertCount(1, $req['5.6.1']);
        $this->assertSame(-1, $req['5.6.1'][0]['line']);
        $this->assertSame(Reason::LIB_CLASS_ADDITION, $req['5.6.1'][0]['reason']);
    }

    public function testClassDetectionWithModeAll()
    {
        $result = new AnalysisResult();
        $analyser = (new StringAnalyser(''))->setResultInstance($result);
        $chg = new LibraryChanges(['mode' => LibraryChanges::MODE_ALL],
            $analyser, $this->getDefaultLibraryInformation());
        $chg->enterNode(new StaticPropertyFetch(new Name('Alpha'), new Name('abc')));
        $limits = $result->getLimits();
        $this->assertCount(1, $limits);
        $this->assertArrayHasKey('5.6.1', $limits);
        $this->assertCount(2, $limits['5.6.1']);
        $this->assertSame(-1, $limits['5.6.1'][0]['line']);
        $this->assertSame(-1, $limits['5.6.1'][1]['line']);
        $this->assertSame(Reason::LIB_CLASS_DEPRECATION, $limits['5.6.1'][0]['reason']);
        $this->assertSame(Reason::LIB_CLASS_REMOVAL, $limits['5.6.1'][1]['reason']);
        $req = $result->getRequirements();
        $this->assertCount(1, $req);
        $this->assertArrayHasKey('5.6.1', $req);
        $this->assertCount(1, $req['5.6.1']);
        $this->assertSame(-1, $req['5.6.1'][0]['line']);
        $this->assertSame(Reason::LIB_CLASS_ADDITION, $req['5.6.1'][0]['reason']);
    }

    public function testClassDetectionWithModeNone()
    {
        $result = new AnalysisResult();
        $analyser = (new StringAnalyser(''))->setResultInstance($result);
        $chg = new LibraryChanges(['mode' => 0b0000],
            $analyser, $this->getDefaultLibraryInformation());
        $chg->enterNode(new StaticCall(new Name('Alpha'), 'abc'));
        $this->assertCount(0, $result->getLimits());
        $this->assertCount(0, $result->getRequirements());
    }

    public function testFunctionDetectionWithModeOnlyAdditionsAndDeprecations()
    {
        $result = new AnalysisResult();
        $analyser = (new StringAnalyser(''))->setResultInstance($result);
        $chg = new LibraryChanges(['mode' => LibraryChanges::MODE_ADDITION | LibraryChanges::MODE_DEPRECATION],
            $analyser, $this->getDefaultLibraryInformation());
        $chg->enterNode(new FuncCall(new Name('a')));
        $limits = $result->getLimits();
        $this->assertCount(1, $limits);
        $this->assertArrayHasKey('5.6.0', $limits);
        $this->assertCount(1, $limits['5.6.0']);
        $this->assertSame(-1, $limits['5.6.0'][0]['line']);
        $this->assertSame(Reason::LIB_FUNCTION_DEPRECATION, $limits['5.6.0'][0]['reason']);
        $req = $result->getRequirements();
        $this->assertCount(1, $req);
        $this->assertArrayHasKey('5.6.0', $req);
        $this->assertCount(1, $req['5.6.0']);
        $this->assertSame(-1, $req['5.6.0'][0]['line']);
        $this->assertSame(Reason::LIB_FUNCTION_ADDITION, $req['5.6.0'][0]['reason']);
    }

    public function testFunctionDetectionWithModeAll()
    {
        $result = new AnalysisResult();
        $analyser = (new StringAnalyser(''))->setResultInstance($result);
        $chg = new LibraryChanges(['mode' => LibraryChanges::MODE_ALL],
            $analyser, $this->getDefaultLibraryInformation());
        $chg->enterNode(new FuncCall('a'));
        $limits = $result->getLimits();
        $this->assertCount(1, $limits);
        $this->assertArrayHasKey('5.6.0', $limits);
        $this->assertCount(2, $limits['5.6.0']);
        $this->assertSame(-1, $limits['5.6.0'][0]['line']);
        $this->assertSame(-1, $limits['5.6.0'][1]['line']);
        $this->assertSame(Reason::LIB_FUNCTION_DEPRECATION, $limits['5.6.0'][0]['reason']);
        $this->assertSame(Reason::LIB_FUNCTION_REMOVAL, $limits['5.6.0'][1]['reason']);
        $req = $result->getRequirements();
        $this->assertCount(1, $req);
        $this->assertArrayHasKey('5.6.0', $req);
        $this->assertCount(1, $req['5.6.0']);
        $this->assertSame(-1, $req['5.6.0'][0]['line']);
        $this->assertSame(Reason::LIB_FUNCTION_ADDITION, $req['5.6.0'][0]['reason']);
    }

    public function testFunctionDetectionWithModeNone()
    {
        $result = new AnalysisResult();
        $analyser = (new StringAnalyser(''))->setResultInstance($result);
        $chg = new LibraryChanges(['mode' => 0b0000],
            $analyser, $this->getDefaultLibraryInformation());
        $chg->enterNode(new FuncCall(new Name('a')));
        $this->assertCount(0, $result->getLimits());
        $this->assertCount(0, $result->getRequirements());
    }

    public function testNoFatalOnDynamicFunctionCall()
    {
        $analyser = new LibraryChanges(['mode' => LibraryChanges::MODE_ADDITION & ~LibraryChanges::MODE_ADDITION]);
        $ast = new FuncCall(new Variable('abc'));
        $analyser->enterNode($ast);
    }

    /**
     * @return \Pvra\InformationProvider\LibraryInformation
     */
    private function getDefaultLibraryInformation()
    {
        return new LibraryInformation([
            'additions' => [
                'function' => [
                    'a' => '5.6.0',
                ],
                'class' => [
                    'Alpha' => '5.6.1',
                ],
                'constant' => [
                    'CONST_1' => '5.10.2',
                ]
            ],
            'removals' => [
                'function' => [
                    'a' => '5.6.0',
                ],
                'class' => [
                    'Alpha' => '5.6.1',
                ],
                'constant' => [
                    'CONST_1' => '5.10.2',
                ]
            ],
            'deprecations' => [
                'function' => [
                    'a' => '5.6.0',
                ],
                'class' => [
                    'Alpha' => '5.6.1',
                ],
                'constant' => [
                    'CONST_1' => '5.10.2',
                ]
            ]
        ]);
    }
}
