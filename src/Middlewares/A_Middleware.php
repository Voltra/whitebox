<?php
/////////////////////////////////////////////////////////////////////////
//Namespace
/////////////////////////////////////////////////////////////////////////
namespace WhiteBox\Middlewares;



/////////////////////////////////////////////////////////////////////////
//Imports
/////////////////////////////////////////////////////////////////////////
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;


/**A class abstracting the shared behavior of middlewares
 * Class A_Middleware
 * @package WhiteBox\Middlewares
 */
abstract class A_Middleware {
    /**Processes the action provided by this middleware
     * @param ServerRequestInterface $rq being the current HTTP request
     * @param ResponseInterface $res being the current HTTP response
     * @param callable $next allowing to go on to the next middleware ; $next($rq, $res) on success
     * @return ResponseInterface
     */
    public abstract function process(ServerRequestInterface $rq, ResponseInterface $res, callable $next) : ResponseInterface;
}