<?php
/////////////////////////////////////////////////////////////////////////
//Namespace
/////////////////////////////////////////////////////////////////////////
namespace WhiteBox\Routing\Abstractions;



/////////////////////////////////////////////////////////////////////////
//Imports
/////////////////////////////////////////////////////////////////////////
use Psr\Http\Message\ResponseInterface;
use WhiteBox\Http\HttpRedirectType;



/**A trait that represents an entity that can execute redirections
 * Trait T_RedirectionManager
 * @package WhiteBox\Routing\Abstractions
 */
trait T_RedirectionManager {
    /////////////////////////////////////////////////////////////////////////
    //Methods
    /////////////////////////////////////////////////////////////////////////
    /**Get a response that will redirect to the given location with the given status
     * @param string $location
     * @param ResponseInterface $res
     * @param null|HttpRedirectType $status
     * @return ResponseInterface
     */
    public function redirect(string $location, ResponseInterface $res, ?HttpRedirectType $status = null): ResponseInterface{
        if(is_null($status))
            $status = HttpRedirectType::FOUND();

        return $res->withHeader("Location", $location)->withStatus($status->getCode());
    }
}