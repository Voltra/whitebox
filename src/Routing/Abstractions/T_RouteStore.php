<?php
/////////////////////////////////////////////////////////////////////////
//Namespace
/////////////////////////////////////////////////////////////////////////
namespace WhiteBox\Routing\Abstractions;



/////////////////////////////////////////////////////////////////////////
//Imports
/////////////////////////////////////////////////////////////////////////
use WhiteBox\Routing\Route;



/**A trait representing objects that can store Route instances
 * Trait T_RouteStore
 * @package WhiteBox\Routing\Abstractions
 */
trait T_RouteStore{
    /////////////////////////////////////////////////////////////////////////
    //Methods
    /////////////////////////////////////////////////////////////////////////
    /**Determines whether or not this T_RouteStores has a given Route (from a method and a regex)
     * @param string $method being the method to check
     * @param string $re being the Regex to check
     * @return bool
     */
    protected abstract function hasRoute(string $method, string $re): bool;

    /**Retrieves a Route in this T_RouteStore from a method and a regex
     * @param string $method being the method of the Route to retrieve
     * @param string $re being the Regex of the Route to retrieve
     * @return Route|null
     */
    protected abstract function getRoute(string $method, string $re): ?Route;

    /**Retrieves the array of Route which method's is the given method
     * @param string $method being the given method
     * @return Route[]
     */
    protected abstract function getRoutesForMethod(string $method): array;


    /**Adds a Route to this T_RouteStore
     * @param Route $route being the route that is being added to this T_RouteStore
     * @return $this
     */
    protected abstract function addRoute(Route $route);
}