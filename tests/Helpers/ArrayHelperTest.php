<?php
namespace Test\Helpers;


use PHPUnit\Framework\TestCase as PHPUnit;
use WhiteBox\Helpers\ArrayHelper;

class ArrayHelperTest extends PHPUnit{
    /**
     * @test
     * @covers ArrayHelper::is_assoc
     */
    public function regularArrayIsNotAssociative(){
        $arr = [0,1,2];
        self::assertFalse(
            ArrayHelper::is_assoc($arr),
            "Even though the given array is a regular array, it is considered as an associative array"
        );
    }


    /**
     * @test
     * @covers ArrayHelper:::is_assoc
     */
    public function associativeArrayIsAssociative(){
        $arr = ["un"=>1, "deux"=>2];
        self::assertTrue(
            ArrayHelper::is_assoc($arr),
            "Even though the given array is associative, it is not considered associative"
        );
    }

    /**
     * @test
     * @covers ArrayHelper::is_assoc()
     */
    public function falseAssociativeIsNotAssociative(){
        $arr = [0=>0, 1=>1];
        self::assertFalse(
            ArrayHelper::is_assoc($arr),
            "Even though the given array is falsely associative, it is considered as an associative array"
        );
    }
}