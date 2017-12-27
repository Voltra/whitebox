<?php
namespace WhiteBox\Routing;


use WhiteBox\Routing\Abstractions\A_CisRouter;

class SubRouter extends A_CisRouter{
    /**Sets up a Route in this T_RouteBuilder
     * @param string $method being the Route's method
     * @param string $re being the Route's regular expression
     * @param callable $functor being the Route's handler
     * @param callable|null $authMiddleware being the Route's middleware
     * @return Route
     */
    protected function setupRoute(string $method, string $re, callable $functor, ?callable $authMiddleware = null): Route {
        if ($this->hasRoute($method, $re)) {
            $route = $this->getRoute($method, $re);
            if (!is_null($route)) {
                $route->setHandler($functor);

                if (!is_null($authMiddleware))
                    $route->setAuthMiddleware($authMiddleware);
            }

            return $route;
            //throw new Exception("It is impossible to override/declare twice a single Route");
        }
        //otherwise
        //push it to the arrays
        $route = new Route($method, $re);
        $this->addRoute($route);
        $route->setHandler($functor);

        if (!is_null($authMiddleware))
            $route->setAuthMiddleware($authMiddleware);

        return $route;
    }

    /**Determines whether or not this T_RouteStores has a given Route (from a method and a regex)
     * @param string $method
     * @param string $re
     * @return bool
     */
    protected function hasRoute(string $method, string $re): bool {
        $paramRoute = new Route($method, $re);
        $routes = $this->routes;
        foreach ($routes as $route) {
            if ($route instanceof Route) {
                if ($route->equals($paramRoute))
                    return true;
            }
        }

        return false;
    }

    /**Retrieves a Route in this T_RouteStore from a method and a regex
     * @param string $method
     * @param string $re
     * @return Route|null
     */
    protected function getRoute(string $method, string $re): ?Route {
        $paramRoute = new Route($method, $re);
        foreach($this->routes as $route){
            if($route instanceof Route){
                if($route->equals($paramRoute))
                    return $route;
            }
        }

        return null;
    }

    /**Retrieves the array of Route which method's is the given method
     * @param string $method being the given method
     * @return Route[]
     */
    protected function getRoutesForMethod(string $method): array {
        if(in_array($method, Route::METHODS, true)){
            return array_filter($this->routes, function(Route $route) use($method){
                return $route->method() === $method;
            });
        }else
            return [];
    }

    /**Adds a Route to this T_RouteStore
     * @param Route $route being the route that is being added to this T_RouteStore
     * @return $this
     */
    protected function addRoute(Route $route) {
        $this->routes[] = $route;
        return $this;
    }
}