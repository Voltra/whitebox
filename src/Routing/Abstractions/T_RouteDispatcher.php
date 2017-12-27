<?php
/////////////////////////////////////////////////////////////////////////
//Namespace
/////////////////////////////////////////////////////////////////////////
namespace WhiteBox\Routing\Abstractions;



/////////////////////////////////////////////////////////////////////////
//Imports
/////////////////////////////////////////////////////////////////////////
use GuzzleHttp\Psr7\ServerRequest;
use Psr\Http\Message\ServerRequestInterface;


/**An interface representing an object that dispatches routes
 * Trait T_RouteDispatcher
 * @package WhiteBox\Routing
 */
trait T_RouteDispatcher{
    /**The protected way to handle a request
     * @param ServerRequestInterface $request being the request to handle
     * @return mixed
     */
    protected abstract function handleRequest(ServerRequestInterface $request);

    /**The public way to handle a request (calls the protected way)
     * @param null|ServerRequestInterface $request being the request to handle
     * @return mixed
     */
    public function run(?ServerRequestInterface $request = null){
        if(is_null($request))
            $request = ServerRequest::fromGlobals();

        return $this->handleRequest($request);
    }
}