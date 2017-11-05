<?php
namespace Test\Helpers;

use PHPUnit\Framework\TestCase as PHPUnit;
use WhiteBox\Helpers\TraitChecker;

trait one{ abstract function get(); }
class Cone{
    use one;
    function get(){}
}

class Ctwo extends Cone{}

trait three{ abstract function set(); }
trait yolo{ abstract function yol(); }
class Cthree{
    use three, yolo;
    function set(){}
    function yol(){}
}

class Cfour extends Cthree{
    use one;
    function get(){}
}

class TraitCheckerTest extends PHPUnit{

    /**
     * @test
     * @covers TraitChecker::getTraitsFrom
     * @covers TraitChecker::getTraits
     */
    public function noTraitsImpliesEmpty(){
        $this->assertEmpty(
            TraitChecker::getTraitsFrom(self::class),
            "Even though a class doesn't use any trait, the traits array isn't empty"
        );
    }

    /**
     * @test
     * @covers TraitChecker::getTraitsFrom
     * @covers TraitChecker::getTraits
     */
    public function directTraitDetected(){
        $this->assertEquals(
            [one::class],
            TraitChecker::getTraitsFrom(Cone::class),
            "Event though a class uses a trait, it is not detected"
        );
    }

    /**
     * @test
     * @covers TraitChecker::getTraitsFrom
     * @covers TraitChecker::getTraits
     */
    public function inheritanceTraitDetected(){
        $this->assertEquals(
            [one::class],
            TraitChecker::getTraitsFrom(Ctwo::class),
            "Even though a class inherits from a class that uses a trait, the first one doesn't have it detected"
        );
    }

    /**
     * @test
     * @covers TraitChecker::classHasTrait
     * @covers TraitChecker::hasTrait
     * @depends directTraitDetected
     */
    public function hasDirectTrait(){
        $this->assertEquals(
            true,
            TraitChecker::classHasTrait(Cone::class, one::class),
            "Even though the class uses the trait, it is not detected"
        );
    }

    /**
     * @test
     * @covers TraitChecker::classHasTrait
     * @covers TraitChecker::hasTrait
     * @depends directTraitDetected
     */
    public function doesntHaveTrait(){
        $this->assertEquals(
            false,
            TraitChecker::classHasTrait(self::class, one::class),
            "Even though the class doesn't use the said trait, it is detected"
        );
    }

    /**
     * @test
     * @covers TraitChecker::classHasTrait
     * @covers TraitChecker::hasTrait
     * @depends directTraitDetected
     */
    public function inheritanceHasTrait(){
        $this->assertEquals(
            true,
            TraitChecker::classHasTrait(Ctwo::class, one::class),
            "Even though the class uses the trait via inheritance, it is not detected"
        );
    }

    /**
     * @test
     * @covers TraitChecker::getTraitsFrom
     * @covers TraitChecker::getTraits
     */
    public function multipleYetCorrect(){
        $arr = [three::class, yolo::class];
        $res = TraitChecker::getTraitsFrom(Cthree::class);
        $this->assertEquals(
            sort($arr),
            sort($res),
            "Even though a class has multiple trait, they are not all detected"
        );
    }

    /**
     * @test
     * @covers TraitChecker::getTraitsFrom
     * @covers TraitChecker::getTraits
     */
    public function multipleInheritanceYetCorrect(){
        $arr = [three::class, yolo::class, one::class];
        $res = TraitChecker::getTraitsFrom(Cthree::class);
        $this->assertEquals(
            sort($arr),
            sort($res),
            "Even though a class has multiple trait (+ via inheritance), they are not all detected"
        );
    }
}