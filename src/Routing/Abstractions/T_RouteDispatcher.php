<?php
/////////////////////////////////////////////////////////////////////////
//Namespace
/////////////////////////////////////////////////////////////////////////
namespace WhiteBox\Routing\Abstractions;



/////////////////////////////////////////////////////////////////////////
//Imports
/////////////////////////////////////////////////////////////////////////
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ServerRequest;
use function Http\Response\send;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;


/**An interface representing an object that dispatches routes
 * Trait T_RouteDispatcher
 * @package WhiteBox\Routing
 */
trait T_RouteDispatcher{
    /////////////////////////////////////////////////////////////////////////
    //Methods
    /////////////////////////////////////////////////////////////////////////
    /**The protected way to handle a request
     * @param ServerRequestInterface $request being the request to handle
     * @param ResponseInterface $response
     * @return mixed
     */
    protected abstract function handleRequest(ServerRequestInterface $request, ResponseInterface $response) : ResponseInterface;

    /**The public way to handle a request (calls the protected way)
     * @param null|ServerRequestInterface $request being the request to handle
     * @param null|ResponseInterface $response
     * @return mixed
     */
    public function run(?ServerRequestInterface $request = null, ?ResponseInterface $response = null){
        if($request === null)
            $request = ServerRequest::fromGlobals();

        if($response === null)
            $response = (new Response())->withStatus(200);

        $finalResponse = $this->handleRequest($request, $response);
        send($finalResponse);
        return $finalResponse;
    }
}