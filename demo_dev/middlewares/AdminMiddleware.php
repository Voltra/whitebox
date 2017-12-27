<?php

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use WhiteBox\App;
use WhiteBox\Helpers\RegexHandler;
use WhiteBox\Middlewares\A_Middleware;

class AdminMiddleware extends A_Middleware{
    protected $app;

    public function __construct(App $app){
        $this->app = $app;
    }

    public function process(ServerRequestInterface $rq, ResponseInterface $res, callable $next): ResponseInterface {
        $uri = $rq->getUri()->getPath();
        $requiresAdmin = new RegexHandler("/^\/admin/");//Uri starts with "/admin"

        if($requiresAdmin->appliesTo($uri)){
            if(!$this->isAdmin())
                return $this->app->redirectTo("home", $res);
        }

        return $next($rq, $res);
    }

    protected function isAdmin(){
        return (bool)rand(0,1);
    }
}