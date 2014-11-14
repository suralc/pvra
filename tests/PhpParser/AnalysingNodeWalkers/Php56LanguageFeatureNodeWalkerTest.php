<?php

namespace Pvra\tests\PhpParser\AnalysingNodeWalkers;


use Pvra\RequirementAnalysis\Result\RequirementCategory;
use Pvra\tests\BaseNodeWalkerTestCase;


class Php56LanguageFeatureNodeWalkerTest extends BaseNodeWalkerTestCase
{
    protected $classToTest = 'Pvra\\PhpParser\AnalysingNodeWalkers\\Php56LanguageFeatureNodeWalker';

    public function testVariadics()
    {
        $res = $this->runInstanceFromScratch('variadics');

        $this->assertSame('5.6.0', $res->getRequiredVersion());
        $this->assertCount(1, $res->getRequirements());
        $this->assertCount(5, $res->getRequirementInfo('5.6.0'));
        $this->assertSame(4, $res->getRequirementInfo('5.6.0')[0]['location']['line']);
        $this->assertSame(8, $res->getRequirementInfo('5.6.0')[1]['location']['line']);
        $this->assertSame(13, $res->getRequirementInfo('5.6.0')[2]['location']['line']);
        $this->assertSame(15, $res->getRequirementInfo('5.6.0')[3]['location']['line']);
        $this->assertSame(20, $res->getRequirementInfo('5.6.0')[4]['location']['line']);
        $this->assertSame(RequirementCategory::FUNCTION_VARIADIC, $res->getRequirementInfo('5.6.0')[0]['category']);
        $this->assertSame(RequirementCategory::FUNCTION_VARIADIC, $res->getRequirementInfo('5.6.0')[1]['category']);
        $this->assertSame(RequirementCategory::FUNCTION_VARIADIC, $res->getRequirementInfo('5.6.0')[2]['category']);
        $this->assertSame(RequirementCategory::FUNCTION_VARIADIC, $res->getRequirementInfo('5.6.0')[3]['category']);
        $this->assertSame(RequirementCategory::FUNCTION_VARIADIC, $res->getRequirementInfo('5.6.0')[4]['category']);
    }
}
