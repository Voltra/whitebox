<?php
namespace Test\Helpers;

use PHPUnit\Framework\TestCase as PHPUnit;
use WhiteBox\Helpers\MagicalArray;

class MagicalArrayTest extends PHPUnit{
    protected $str;

    public function __construct($name = null, array $data = [], $dataName = ''){
        parent::__construct($name, $data, $dataName);
        $this->str = "";
    }

    /**
     * @test
     * @covers MagicalArray::__construct
     */
    public function noneIsEmpty(){
        self::assertEquals(
            0,
            count(new MagicalArray()),
            "Even though the MagicalArray was created without arguments, it is not empty"
        );

        self::assertEquals(
            0,
            count(new MagicalArray([])),
            "Even though the MagicalArray was created with an empty array, it is not empty"
        );
    }

    /**
     * @test
     * @covers MagicalArray::__construct
     */
    public function paramSameAsMagical(){
        $arr = [0,1,2];
        $mag = new MagicalArray($arr);

        for($i = 0 ; $i < count($arr) ; $i+=1)
            self::assertEquals(
                $arr[$i],
                $mag[$i],
                "Even though it has been created from this array, the MagicalArray is not the same"
            );
    }

    /**
     * @test
     * @covers MagicalArray::toJson
     * @covers MagicalArray::fromJson
     * @depends paramSameAsMagical
     */
    public function jsonSerializable(){
        $a = new MagicalArray([0,1,2]);
        $b = MagicalArray::fromJson( $a->toJson() );

        self::assertEquals(
            $a,
            $b,
            "Serialize->Deserialize doesn't work :x"
        );
    }

    /**
     * @test
     * @covers MagicalArray::__construct
     * @depends paramSameAsMagical
     */
    public function nonPresentGivesDefault(){
        $arr = new MagicalArray([0,1,2], "AH");

        self::assertEquals(
            "AH",
            $arr(42),
            "Even though the key is not in the array, it doesn't give the default value"
        );
    }

    public function testFilter(){
        $arr = new MagicalArray([1,2,3,4,5,6]);
        $res = new MagicalArray([2,4,6]);

        self::assertEquals(
            $res,
            $arr->filter(function($elem){ return $elem%2==0; }),
            "'filter' does not work properly"
        );
    }

    public function testMap(){
        $arr = new MagicalArray([1,2,3]);
        $res = new MagicalArray([2,4,6]);

        self::assertEquals(
            $res,
            $arr->map(function($elem){ return $elem*2; }),
            "'map' doesn't work properly"
        );
    }

    public function testReduce(){
        $arr = new MagicalArray([1,2,3]);
        self::assertEquals(
            6,
            $arr->reduce(function($acc, $elem){ return $elem+$acc; }, 0),
            "'reduce' doesn't work properly"
        );
    }


    public function testForeach(){
        $arr = new MagicalArray([1,2,3]);
        $this->str = "";

        $arr->forEach(function($elem){
            $this->str .= "{$elem}";
        });

        self::assertEquals(
            "123",
            $this->str,
            "'forEach' doesn't work properly"
        );
    }
}