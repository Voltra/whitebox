<?php
use PHPUnit\Framework\TestCase as PHPUnit;
use WhiteBox\Helpers\Enums\TypedEnum;

class FloatEnum extends TypedEnum{
    public static function isOfCorrectType($value): bool{
        return (bool)is_float($value);
    }
}

/**
 * Class PI
 * @method static PI VAL
 * @method static PI VALUE
 */
class PI extends FloatEnum{
    const VAL = "3.14";
    const VALUE = 3.14;
}

class TypedEnumTest extends PHPUnit{
    /**
     * @test
     * @covers TypedEnum::isOfCorrectType
     */
    public function availableValuesAreOfCorrectType(){
        $arr = PI::toArray();
        $areOfCorrectType = array_reduce($arr, function($acc, $elem){
            return $acc && is_float($elem);
        }, true);

        self::assertTrue(
            $areOfCorrectType,
            "Even though it is a typed enum, all the defined values are not of the correct type"
        );
    }

    /**
     * @test
     * @covers TypedEnum::__callStatic
     * @expectedException Throwable
     */
    public function cannotGetInvalidValue(){
        $val = PI::VAL();
    }

    /**
     * @test
     * @covers TypedEnum::__construct
     * @expectedException Throwable
     */
    public function cannotInstantiateInvalidValue(){
        $val = new PI(PI::VAL);
    }
}