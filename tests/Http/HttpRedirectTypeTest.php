<?php
namespace Test\Http;

use PHPUnit\Framework\TestCase as PHPUnit;
use WhiteBox\Http\HttpRedirectType;

class HttpRedirectTypeTest extends PHPUnit{
    /**
     * @test
     * @covers HttpRedirectType::getCode
     * @covers HttpRedirectType::PERMANENT
     */
    public function codeOfPermanentIs301(){
        self::assertEquals(
            301,
            HttpRedirectType::PERMANENT()->getCode(),
            "Even though PERMANENT is declared as 301, the code returned isn't 301"
        );
    }

    /**
     * @test
     * @covers HttpRedirectType::getCode
     * @covers HttpRedirectType::FOUND
     */
    public function codeOfFoundIs302(){
        self::assertEquals(
            302,
            HttpRedirectType::FOUND()->getCode(),
            "Even though FOUND is declared as 302, the code returned isn't 302"
        );
    }
}