<?php
namespace Test\Helpers;

use PHPUnit\Framework\TestCase as PHPUnit;
use WhiteBox\Helpers\NamespaceResolver;

class NamespaceResolverTest extends PHPUnit{
    /**
     * @test
     * @covers NamespaceResolver::getFrom
     */
    public function getsCorrectWhenExists(){
        self::assertEquals(
            "Test\\Helpers",
            NamespaceResolver::getFrom(self::class),
            "Even though we passed a defined class (in a namespace), the retrieved namespace is incorrect"
        );
    }

    public function getsNullWhenUndefined(){
        self::assertNull(
            NamespaceResolver::getFrom("undefined"),
            "Even though we passed an undefined class, the retrieved namespace isn't null"
        );
    }
}