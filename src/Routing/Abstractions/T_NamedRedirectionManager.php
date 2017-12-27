<?php
namespace WhiteBox\Routing\Abstractions;


use Psr\Http\Message\ResponseInterface;
use WhiteBox\Http\HttpRedirectType;

trait T_NamedRedirectionManager {
    use T_RouteStore;
    use T_RedirectionManager;

    /**Redirects to a route via its name
     * @param string $routeName
     * @param ResponseInterface $res
     * @param null|HttpRedirectType $status
     * @return ResponseInterface
     */
    public function redirectTo(string $routeName, ResponseInterface $res, ?HttpRedirectType $status = null): ResponseInterface{
        return $this->redirect($this->urlFor($routeName), $res, $status);
    }
}