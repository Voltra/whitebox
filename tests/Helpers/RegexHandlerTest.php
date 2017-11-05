<?php
namespace Test\Helpers;

use PHPUnit\Framework\TestCase as PHPUnit;
use WhiteBox\Helpers\RegexHandler;

class RegexHandlerTest extends PHPUnit{
    /**
     * @test
     * @covers RegexHandler::getGroups
     */
    public function anythingAppliesToEmpty(){
        $re = new RegexHandler("/.*/");
        $this->assertEquals(
            true,
            $re->appliesTo(""),
            "Anything doesn't match the empty string"
        );
    }

    /**
     * @test
     * @covers RegexHandler::getGroups
     */
    public function noMatchImpliesEmptyGroups(){
        $re = new RegexHandler("/([a-z]+)/");
        $this->assertEmpty(
            $re->getGroups("123456789"),
            "Despite the fact that there are no matches, the group array is not empty"
        );
    }

    /**
     * @test
     * @covers RegexHandler::getGroups
     */
    public function groupsAreOrderedProperly(){
        $re = new RegexHandler("/\/user\/(\w+)\/(\d+)/");
        $str = "/user/batman/42";

        $this->assertEquals(
            ["batman", "42"],
            $re->getGroups($str),
            "If a regex has multiple capturing groups, results are not in the correct order"
        );
    }
}