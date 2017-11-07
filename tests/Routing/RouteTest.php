<?php
namespace Test\Routing;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase as PHPUnit;
use WhiteBox\Routing\Route;

class RouteTest extends PHPUnit{
    /**
     * @test
     * @expectedException InvalidArgumentException
     * @covers Route::__construct
     */
    public function wrongMethodThrowsError(){
        $var = new Route("azerty", "404");
    }

    /**
     * @test
     * @covers Route::getHandler
     */
    public function noFunctorMeansNoHandler(){
        $route = new Route("GET", "404");

        self::assertNull(
            $route->getHandler(),
            "Despite the fact that no handler was set, the route has a handler different from null"
        );
    }

    /**
     * @test
     * @covers Route::equals
     */
    public function differentMethodIsNotEqual(){
        $a = new Route("GET", "/");
        $b = new Route("POST", "/");

        self::assertFalse(
            $a->equals($b),
            "Despite the fact that the HTTP Method is different, the Route are equivalent"
        );
    }

    /**
     * @test
     * @covers Route::equals
     */
    public function differentPathIsNotEqual(){
        $a = new Route("GET", "/");
        $b = new Route("GET", "/user");

        self::assertFalse(
            $a->equals($b),
            "Despite the fact that both RE are different, the Route are considered equivalent"
        );
    }

    /**
     * @test
     * @covers Route::equals
     */
    public function equalIsEqual(){
        $a = new Route("GET", "/");
        $b = new Route("GET", "/");

        self::assertTrue(
            $a->equals($b),
            "Despite the fact that both Route have the same method and RE, they are considered different"
        );
    }

    /**
     * @test
     * @covers Route::equals
     */
    public function strictEqualIsEqual(){
        $a = new Route("GET", "/");
        $b = new Route("GET", "/");
        $a->name("/");
        $b->name("/");

        self::assertTrue(
            $a->equals($b, true),
            "With strict mode on, routes with same names, methods and REs are considered different"
        );
    }
}