<?php

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use WhiteBox\Middlewares\A_Middleware;

class ChangesNothingMiddleware extends A_Middleware {
    public function process(ServerRequestInterface $rq, ResponseInterface $res, callable $next): ResponseInterface {
        //echo "changed nothing";
        return $next($rq, $res);
    }
}