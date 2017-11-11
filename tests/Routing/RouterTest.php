<?php
namespace Test\Routing;

use phpmock\MockBuilder;
use PHPUnit\Framework\TestCase as PHPUnit;
use WhiteBox\Helpers\NamespaceResolver;
use WhiteBox\Routing\Router;

class RouterTest extends PHPUnit{
    public static $NAMESPACE;

    public static $location;

    public function setUp(){
        parent::setUp();
        self::$NAMESPACE = NamespaceResolver::getFrom(Router::class);
    }

    public static function mock(string $name, callable $functor, string $namespace = ""){
        return (new MockBuilder())
            ->setNamespace($namespace)
            ->setName($name)
            ->setFunction($functor)
            ->build();
    }

    /**
     * @test
     * @covers Router::urlFor
     */
    public function urlForNonExistingRoute(){
        $app = new Router();
        self::assertEquals(
            "",
            $app->urlFor("unit.test"),
            "Despite the fact that there's no route which such name, the URL is different from the empty string"
        );
    }

    /**
     * @test
     * @covers Router::urlFor
     */
    public function urlForExistingRoute(){
        $app = new Router();
        $app->get("/", function(){})->name("unit.test");

        self::assertEquals(
            "/",
            $app->urlFor("unit.test")
        );
    }

    /**
     * @test
     * @covers Router::urlFor
     * @covers Router::redirect
     */
    public function redirectRedirects(){
        $mock = self::mock(
            "header",
            function(string $str){
                //return "{$str}";
                self::$location = $str;
            },
            self::$NAMESPACE
        );

        $mock->enable();
        $app = new Router();
        $app->redirect("/");
        self::assertEquals(
            "Location: /",
            self::$location,
            "The redirection fails : wrong url assigned"
        );
        $mock->disable();
    }

    /**
     * @test
     * @covers Router::urlFor
     * @covers Router::redirect
     * @covers Router::redirectTo
     */
    public function redirectsToWorksProperly(){
        $mock = self::mock(
            "header",
            function(string $str){
                //return "{$str}";
                self::$location = $str;
            },
            self::$NAMESPACE
        );
        $mock->enable();

        $app = new Router();
        $app->get("/", function(){})->name("unit.test");

        $app->redirectTo("unit.test");
        self::assertEquals(
            "Location: /",
            self::$location,
            "Something wrong happens while redirecting from a route's name"
        );

        $mock->disable();
    }

    //Before going further, test the whole WhiteBox\Http namespace
}
