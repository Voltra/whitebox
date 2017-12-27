<?php
namespace WhiteBox\Middlewares;


use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

abstract class A_Middleware {
    public abstract function process(ServerRequestInterface $rq, ResponseInterface $res ,callable $next) : ResponseInterface;
}