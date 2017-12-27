<?php
namespace WhiteBox\Middlewares;


use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use WhiteBox\App;

trait T_MiddlewareHub {
    /**
     * @var A_Middleware[]
     */
    protected $middlewares;

    /**
     * @var int
     */
    protected $index;

    public function __construct(){
        $this->middlewares = [];
        $this->index = 0;
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

    protected function getCurrent(): ?A_Middleware{
        if(isset($this->middlewares[$this->index]))
            return $this->middlewares[$this->index];

        return null;
    }

    public function process(ServerRequestInterface $rq, ResponseInterface $res): ResponseInterface{
        $current = $this->getCurrent();
        $this->index += 1;

        if(is_null($current))
            return $res;
        else
            return $current->process($rq, $res, [$this, "process"]);
    }
}