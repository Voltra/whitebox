<?php
namespace WhiteBox\Middlewares;


use Psr\Http\Message\ServerRequestInterface;
use WhiteBox\App;

trait T_MiddlewareHub {
    /**
     * @var A_Middleware[]
     */
    protected $middlewares;

    public function __construct(){
        $this->middlewares = [];
    }

    protected function has(A_Middleware $middleware) : bool{
        $class = get_class($middleware);
        $classes = array_map(function(A_Middleware $m){
            return get_class($m);
        }, $this->middlewares);

        return in_array($class, $classes);
    }

    public function pipe(A_Middleware $middleware){
        if(!$this->has($middleware))
            $this->middlewares[] = $middleware;

        return $this;
    }

    public function process(ServerRequestInterface $rq, App $app){
        function hasNext(array $arr) : bool{
            return next($arr) !== false;
        }

        function getNext(array $arr){
            return hasNext($arr) ? next($arr) : null;
        }

        foreach($this->middlewares as $middleware)
            $middleware->process($rq, getNext($this->middlewares), $app);
    }
}