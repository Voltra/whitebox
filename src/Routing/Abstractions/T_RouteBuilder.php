<?php
/////////////////////////////////////////////////////////////////////////
//Namespace
/////////////////////////////////////////////////////////////////////////
namespace WhiteBox\Routing\Abstractions;



/////////////////////////////////////////////////////////////////////////
//Imports
/////////////////////////////////////////////////////////////////////////
use WhiteBox\Routing\Route;



/**Represents objects that can build Route instances
 * Trait T_RouteBuilder
 * @package WhiteBox\Routing\Abstractions
 */
trait T_RouteBuilder{
    /////////////////////////////////////////////////////////////////////////
    //Methods
    /////////////////////////////////////////////////////////////////////////
    /**Sets up a Route in this T_RouteBuilder
     * @param string $method being the Route's method
     * @param string $re being the Route's regular expression
     * @param callable $functor being the Route's handler
     * @param callable|null $authMiddleware being the Route's middleware
     * @return Route
     */
    protected abstract function setupRoute(string $method, string $re, callable $functor, ?callable $authMiddleware = null): Route;

    /** Replaces the handler for the error 404 page
     * @param $functor - the functor called when a page is not found
     * @return Route
     */
    public function error404(callable $functor): Route{
        return $this->setupRoute("error", "404", $functor);
    }

    /** Replaces the handler for the given route when accessed via the GET method
     * @param string $route - a string designating the complete route (including the website root, eg. "/user")
     * @param callable $functor - a callable object/function to be called when the route is requested
     *
     * @param callable|null $authMiddleware
     * @return Route
     */
    public function get(string $route, callable $functor, ?callable $authMiddleware = null): Route{
        return $this->setupRoute("GET", $route, $functor, $authMiddleware);
    }

    /** Replaces the handler for the given route when accessed via the POST method
     * @param string $route - a string designating the complete route (including the website root, eg. "/user")
     * @param callable $functor - a callable object/function to be called when the route is requested
     *
     * @param callable|null $authMiddleware
     * @return Route
     */
    public function post(string $route, callable $functor, ?callable $authMiddleware = null): Route{
        return $this->setupRoute("POST", $route, $functor, $authMiddleware);
    }

    /** Replaces the handler for the given route when accessed via the PUT method
     * @param string $route - a string designating the complete route (including the website root, eg. "/user")
     * @param callable $functor - a callable object/function to be called when the route is requested
     *
     * @param callable|null $authMiddleware
     * @return Route
     */
    public function put(string $route, callable $functor, ?callable $authMiddleware = null): Route{
        return $this->setupRoute("PUT", $route, $functor, $authMiddleware);
    }

    /** Replaces the handler for the given route when accessed via the HEAD method
     * @param string $route - a string designating the complete route (including the website root, eg. "/user")
     * @param callable $functor - a callable object/function to be called when the route is requested
     *
     * @param callable|null $authMiddleware
     * @return Route
     */
    public function head(string $route, callable $functor, ?callable $authMiddleware = null): Route{
        return $this->setupRoute("HEAD", $route, $functor, $authMiddleware);
    }
}