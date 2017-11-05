<?php
namespace WhiteBox\Routing;

use WhiteBox\Helpers\RegexHandler;
use WhiteBox\Http\Request;
use WhiteBox\Routing\Route;

class Router{
    protected $routes;
    protected $handlers;

    protected static $wildcards = [
        "/:ALNUM/" => "([A-Z0-9_]+)",
        "/:alnum/" => "([a-z0-9_]+)",
        "/:word/" => "(\w+)",
        "/:digit[^s]*/" => "(\d)", //only matches "digit" and not "digits" => digit not followed by an s
        "/:digits/" => "(\d+)",
        "/:alpha/" => "([a-z]+)",
        "/:ALPHA/" => "([A-Z]+)"
    ];

    private static $coreWildcards = [
        "/:ALNUM/",
        "/:alnum/",
        "/:word/",
        "/:digit[^s]*/",
        "/:digits/",
        "/:alpha/",
        "/:ALPHA/"
    ];

    public static function registerWildcard(string $wildcard, string $regex){
        if(!array_key_exists($wildcard, self::$wildcards))
            self::$wildcards[$wildcard] = $regex;
    }

    public static function removeWildcard(string $wildcard){
        if(
            !array_key_exists($wildcard, self::$coreWildcards)
            && array_key_exists($wildcard, self::$wildcards)
        )
            unset(self::$wildcards[$wildcard]);
    }

    public static function registerAliasWildcard(string $alias, string $current){
        if(
            !array_key_exists($alias, self::$wildcards)
            && array_key_exists($current, self::$wildcards)
        )
            self::registerWildcard($alias, self::$wildcards[$current]);
    }

    public function __construct(){
        $this->routes = [
            new Route("error", "404", function(){ echo "Error 404"; })
        ];
    }

    public static function makeRegex(string $uri_regex){
        return "/^" . str_replace("/", "\/", self::masksToRegex($uri_regex)) . "$/";
    }

    protected static function masksToRegex(string $uri_regex){
        $re = "{$uri_regex}";

        foreach(self::$wildcards as $pattern=>$replacement) //Replaces all defined wildcards before default wildcard
            $re = preg_replace($pattern, $replacement, $re);

        return preg_replace("/:(\w+)/i", "([^/]+)", $re);
    }

    public static function uriParams(string $uri, string $uri_regex){
        $uri_regex = self::masksToRegex($uri_regex);
        $regex = new RegexHandler(self::makeRegex($uri_regex));

        return $regex->getGroups($uri);
    }

    protected function hasRoute(string $method, string $re){
        $paramRoute = new Route($method, $re);
        foreach($this->routes as $route){
            if($route instanceof Route){
                if($route->equals($paramRoute))
                    return true;
            }
        }

        return false;
    }

    protected function getRoute(string $method, string $re){
        $paramRoute = new Route($method, $re);
        foreach($this->routes as $route){
            if($route instanceof Route){
                if($route->equals($paramRoute))
                    return $route;
            }
        }

        return null;
    }

    protected function getRoutesForMethod(string $method){
        if(in_array($method, Route::$METHODS, true)){
            return array_filter($this->routes, function(Route $route) use($method){
                return $route->method() === $method;
            });
        }else
            return [];
    }

    protected function setupRoute(string $method, string $re, callable $functor){
        if($this->hasRoute($method, $re)){
            $route =  $this->getRoute($method, $re);
            if($route!=null && $route->hasHandler()){
                $route->setHandler($functor);
            }

            return $route;
        }else{
            //push it to the arrays
            $this->routes[] = new Route($method, $re);
            $route = $this->getRoute($method, $re);
            $route->setHandler($functor);

            return $route;
        }
    }

    /** Replaces the handler for the error 404 page
     * @param $functor - the functor called when a page is not found
     *
     * @return Route
     */
    public function error404(callable $functor){
        return $this->setupRoute("error", "404", $functor);
    }

    /** Replaces the handler for the given route when accessed via the GET method
     * @param $route - a string designating the complete route (including the website root)
     * @param $functor - a callable object/function to be called when the route is requested
     *
     * @return Route
     */
    public function get(string $route, callable $functor){
        return $this->setupRoute("GET", $route, $functor);
    }

    /** Replaces the handler for the given route when accessed via the POST method
     * @param $route - a string designating the complete route (including the website root)
     * @param $functor - a callable object/function to be called when the route is requested
     *
     * @return Route
     */
    public function post(string $route, callable $functor){
        return $this->setupRoute("POST", $route, $functor);
    }

    /** Replaces the handler for the given route when accessed via the PUT method
     * @param $route - a string designating the complete route (including the website root)
     * @param $functor - a callable object/function to be called when the route is requested
     *
     * @return Route
     */
    public function put(string $route, callable $functor){
        return $this->setupRoute("PUT", $route, $functor);
    }

//    /** Replaces the handler for the given route when accessed via the DELETE method
//     * @param $route - a string designating the complete route (including the website root)
//     * @param $functor - a callable object/function to be called when the route is requested
//     *
//     * @return Route
//     */
//    public function delete($route, $functor){
//            return $this->setupRoute("DELETE", $route, $functor);
//    }

    /** Replaces the handler for the given route when accessed via the HEAD method
     * @param $route - a string designating the complete route (including the website root)
     * @param $functor - a callable object/function to be called when the route is requested
     *
     * @return Route
     */
    public function head(string $route, callable $functor){
        return $this->setupRoute("HEAD", $route, $functor);
    }


    protected function handleRequests(){
        $request = new Request();
        $method = $request->getMethod();
        $routes = $this->getRoutesForMethod($method);
        $uri = $request->requestURI();

        $route = array_reduce($routes, function($acc, Route $route) use($uri){
            $regex = new RegexHandler(self::makeRegex($route->regex()));
            if($regex->appliesTo($uri))
                $acc = $route;

            return $acc;
        }, null);

        if(is_null($route))
            $route = $this->getRoute("error", "404"); //One must always be defined


        $arguments = self::uriParams($uri, $route->regex());
        $arguments = array_merge($arguments, [
            $request
        ]);


        call_user_func_array($route->getHandler(), $arguments);
    }

    public function run(){
        $this->handleRequests();
    }

    public function urlFor(string $routeName){
        $name = "{$routeName}";

        foreach($this->routes as $route){
            if($route->getName() === $name)
                return $route->uri();
        }

        return "";
    }

    public function redirect(string $url){
        header("Location: {$url}");
    }

    public function redirectTo(string $routeName){
        $this->redirect( $this->urlFor($routeName) );
    }
}