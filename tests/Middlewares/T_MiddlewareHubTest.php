<?php
use PHPUnit\Framework\TestCase as PHPUnit;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use WhiteBox\App;
use WhiteBox\Middlewares\A_Middleware;
use WhiteBox\Middlewares\T_MiddlewareHub;

class MiddlewareHub{
    use T_MiddlewareHub;
}

class T_MiddlewareHubTest extends PHPUnit{
    /**
     * @var T_MiddlewareHub
     */
    protected $hub;
    protected $app;

    public function setUp() {
        parent::setUp();
        $this->hub = new MiddlewareHub();
        $this->app = new App;
    }

    protected function getLength(MiddlewareHub $hub=null) : int{
        if(is_null($hub))
            $hub = $this->hub;

        $MiddlewareHub = new ReflectionClass(MiddlewareHub::class);
        $middlewares = $MiddlewareHub->getProperty("middlewares");
        $middlewares->setAccessible(true);
        $ret = $middlewares->getValue($hub);
        $middlewares->setAccessible(false);
        return count($ret);
    }

    /**
     * @test
     * @covers T_MiddlewareHub::pipe
     */
    public function canAddMiddlewareIfNone(){
        $this->hub->pipe(new class extends A_Middleware{
            public function process(ServerRequestInterface $rq, ResponseInterface $res, callable $next): ResponseInterface {
                return $res;
            }
        });

        self::assertEquals(1, $this->getLength());
    }

    /**
     * @test
     * @covers T_MiddlewareHub::pipe
     */
    public function canAddDifferentMiddlewares(){
        $this->hub
        ->pipe(new MiddlewareA())
        ->pipe(new MiddlewareB());

        self::assertEquals(2, $this->getLength());
    }

    /**
     * @test
     * @covers T_MiddlewareHub::pipe
     */
    public function canAddDifferentMiddlewaresViaInheritance(){
        $this->hub
        ->pipe(new MiddlewareB())
        ->pipe(new MiddlewareC());

        self::assertEquals(2, $this->getLength());
    }

    /**
     * @test
     * @covers T_MiddlewareHub::pipe
     */
    public function cantAddTwiceFromSameMiddlewareClass(){
        $this->hub
        ->pipe(new MiddlewareA())
        ->pipe(new MiddlewareA());

        self::assertEquals(1, $this->getLength());
    }
}


class MiddlewareA extends A_Middleware{

    public function process(ServerRequestInterface $rq, ResponseInterface $res, callable $next): ResponseInterface {
        return $res;
    }
}

class MiddlewareB extends A_Middleware{

    public function process(ServerRequestInterface $rq, ResponseInterface $res, callable $next): ResponseInterface {
        return $res;
    }
}

class MiddlewareC extends MiddlewareB{
    public function process(ServerRequestInterface $rq, ResponseInterface $res, callable $next): ResponseInterface {
        return $res;
    }
}