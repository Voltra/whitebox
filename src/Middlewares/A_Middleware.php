<?php
namespace WhiteBox\Middlewares;


use Psr\Http\Message\ServerRequestInterface;
use WhiteBox\App;

abstract class A_Middleware {
    public abstract function process(ServerRequestInterface $rq, ?A_Middleware $next, App $app);
}