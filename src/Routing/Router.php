<?php
namespace WhiteBox\Routing;

use WhiteBox\Helpers\RegexHandler;
use WhiteBox\Http\Request;
use WhiteBox\Routing\Route;

/**A class representing a router in a routing system
 * Class Router
 * @package WhiteBox\Routing
 */
class Router{
    /////////////////////////////////////////////////////////////////////////
    //Properties
    /////////////////////////////////////////////////////////////////////////
    /**The routes registered in this Router
     * @var array
     */
    protected $routes;



    /////////////////////////////////////////////////////////////////////////
    //Class properties
    /////////////////////////////////////////////////////////////////////////
    /**The default wildcards used to ease the regex experience for routes
     * @var array
     */
    protected static $wildcards = [
        "/:ALNUM/" => "([A-Z0-9_]+)",
        "/:alnum/" => "([a-z0-9_]+)",
        "/:word/" => "(\w+)",
        "/:digit[^s]*/" => "(\d)", //only matches "digit" and not "digits" => digit not followed by an s
        "/:digits/" => "(\d+)",
        "/:alpha/" => "([a-z]+)",
        "/:ALPHA/" => "([A-Z]+)"
    ];

    /**The core wildcards (keys)
     * @var array
     */
    private static $coreWildcards = null;

    /**Determines whether or not the Router's core wildcards are registered or not
     * @var bool
     */
    private static $isInitialized = false;

    /////////////////////////////////////////////////////////////////////////
    //Class methods
    /////////////////////////////////////////////////////////////////////////
    /**A static method initializing the core wildcards if they are not initialized
     */
    public static function initCore(){
        if(!self::$isInitialized) {
            self::$coreWildcards = array_keys(self::$wildcards);
            self::$isInitialized = true;
        }
    }

    /**Registers a wildcard (only if it doesn't exist)
     * @param string $wildcard being the wildcard identifier/non-compiled regex (eg. "/:wildcard/")
     * @param string $regex being the compiled regex for the wildcard
     */
    public static function registerWildcard(string $wildcard, string $regex){
        if(!array_key_exists($wildcard, self::$wildcards))
            self::$wildcards[$wildcard] = $regex;
    }

    /**Removes a (non core) wildcard
     * @param string $wildcard being the wildcard identifier/non-compiled regex of the wildcard to remove
     */
    public static function removeWildcard(string $wildcard){
        if(
            !array_key_exists($wildcard, self::$coreWildcards)
            && array_key_exists($wildcard, self::$wildcards)
        )
            unset(self::$wildcards[$wildcard]);
    }

    /**Registers a wildcard as an alias of an already registered wildcard
     * @param string $alias being the wildcard identifier/non-compiled regex of the new wildcard
     * @param string $current being the the wildcard identifier/non-compiled regex of the aliased wildcard
     */
    public static function registerAliasWildcard(string $alias, string $current){
        if(
            !array_key_exists($alias, self::$wildcards)
            && array_key_exists($current, self::$wildcards)
        )
            self::registerWildcard($alias, self::$wildcards[$current]);
    }

    /**Creates a regex from a Route's regex
     * @param string $uri_regex being the Route's regex
     * @return string
     */
    public static function makeRegex(string $uri_regex){
        return "/^" . str_replace("/", "\/", self::masksToRegex($uri_regex)) . "$/";
    }

    /**Converts any non-compiled regex (wildcard) to compiled regex and return the modified string
     * @param string $uri_regex being the Route's regex
     * @return string
     */
    protected static function masksToRegex(string $uri_regex){
        $re = "{$uri_regex}";

        foreach(self::$wildcards as $pattern=>$replacement) //Replaces all defined wildcards before default wildcard
            $re = preg_replace($pattern, $replacement, $re);

        return (string)preg_replace("/:(\w+)/i", "([^/]+)", $re);
    }

    /**Retrieves the array of URI parameters from the URI dans the Route's regex
     * @param string $uri being the requested URI
     * @param string $uri_regex being the Route's regex
     * @return array
     */
    public static function uriParams(string $uri, string $uri_regex){
        $uri_regex = self::masksToRegex($uri_regex);
        $regex = new RegexHandler(self::makeRegex($uri_regex));

        return $regex->getGroups($uri);
    }



    /////////////////////////////////////////////////////////////////////////
    //Magics
    /////////////////////////////////////////////////////////////////////////
    /**Construct a Router
     * Router constructor.
     */
    public function __construct(){
        $this->routes = [
            new Route("error", "404", function(){ echo "<b>Error 404</b>"; })
        ];
    }

    /**Determines whether or not this Router has a given Route (from a method and a regex)
     * @param string $method
     * @param string $re
     * @return bool
     */
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

    /**Retrieves a Route in this Router from a method and a regex
     * @param string $method
     * @param string $re
     * @return Route|null
     */
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

    /**Retrieves the array of Route which method's is the given method
     * @param string $method being the given method
     * @return array
     */
    protected function getRoutesForMethod(string $method){
        if(in_array($method, Route::METHODS, true)){
            return array_filter($this->routes, function(Route $route) use($method){
                return $route->method() === $method;
            });
        }else
            return [];
    }

    /**Sets up a Route in this Router
     * @param string $method being the Route's method
     * @param string $re being the Route's regular expression
     * @param callable $functor being the Route's handler
     * @param callable|null $authMiddleware being the Route's middleware
     * @return null|\WhiteBox\Routing\Route
     */
    protected function setupRoute(string $method, string $re, callable $functor, callable $authMiddleware = null){
        if($this->hasRoute($method, $re)){
            $route =  $this->getRoute($method, $re);
            if(!is_null($route)) {
                $route->setHandler($functor);

                if(!is_null($authMiddleware))
                    $route->setAuthMiddleware($authMiddleware);
            }

            return $route;
        }
        //otherwise
        //push it to the arrays
        $this->routes[] = new Route($method, $re);
        $route = $this->getRoute($method, $re);
        $route->setHandler($functor);

        if(!is_null($authMiddleware))
            $route->setAuthMiddleware($authMiddleware);

        return $route;
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
     * @param string $route - a string designating the complete route (including the website root, eg. "/user")
     * @param callable $functor - a callable object/function to be called when the route is requested
     *
     * @param callable|null $authMiddleware
     * @return \WhiteBox\Routing\Route
     */
    public function get(string $route, callable $functor, callable $authMiddleware = null){
        return $this->setupRoute("GET", $route, $functor, $authMiddleware);
    }

    /** Replaces the handler for the given route when accessed via the POST method
     * @param string $route - a string designating the complete route (including the website root, eg. "/user")
     * @param callable $functor - a callable object/function to be called when the route is requested
     *
     * @param callable|null $authMiddleware
     * @return \WhiteBox\Routing\Route
     */
    public function post(string $route, callable $functor, callable $authMiddleware = null){
        return $this->setupRoute("POST", $route, $functor, $authMiddleware);
    }

    /** Replaces the handler for the given route when accessed via the PUT method
     * @param string $route - a string designating the complete route (including the website root, eg. "/user")
     * @param callable $functor - a callable object/function to be called when the route is requested
     *
     * @param callable|null $authMiddleware
     * @return \WhiteBox\Routing\Route
     */
    public function put(string $route, callable $functor, callable $authMiddleware = null){
        return $this->setupRoute("PUT", $route, $functor, $authMiddleware);
    }

    /** Replaces the handler for the given route when accessed via the HEAD method
     * @param string $route - a string designating the complete route (including the website root, eg. "/user")
     * @param callable $functor - a callable object/function to be called when the route is requested
     *
     * @param callable|null $authMiddleware
     * @return \WhiteBox\Routing\Route
     */
    public function head(string $route, callable $functor, callable $authMiddleware = null){
        return $this->setupRoute("HEAD", $route, $functor, $authMiddleware);
    }


    /**Bootstraps this Router to handle requests
     */
    protected function handleRequests(){
        $request = Request::fromGlobals();
        $method = $request->getMethod();
        $routes = $this->getRoutesForMethod($method);
        $uri = $request->requestURI();

        $route = array_reduce($routes, function($acc, Route $route) use($uri){
            $regex = new RegexHandler( self::makeRegex($route->regex()) );
            if($regex->appliesTo($uri))
                $acc = $route;

            return $acc;
        }, null);

        if(is_null($route))
            $route = $this->getRoute("error", "404"); //One must always be defined
        else if(!call_user_func($route->getAuthMiddleware(), $request))
            $route = $this->getRoute("error", "404");


        $arguments = self::uriParams($uri, $route->regex());
        $arguments = array_merge($arguments, [
            $request
        ]);


        call_user_func_array($route->getHandler(), $arguments);
    }

    /**Runs this Router
     */
    public function run(){
        $this->handleRequests();
    }

    /**Retrieves the regex/URL associated to the Route that has the given routeName
     * @param string $routeName being the name of the Route to lookup the url for
     * @return string
     */
    public function urlFor(string $routeName){
        $name = "{$routeName}";

        foreach($this->routes as $route){
            if($route->getName() === $name)
                return $route->regex();
        }

        return "";
    }

    /**Redirects to a given URL
     * @param string $url being the URL to redirect to
     */
    public function redirect(string $url){
        return header("Location: {$url}");
    }

    /**Redirects to the URL of the Route that has the given routeName
     * @param string $routeName being the name of the Route to redirect to
     */
    public function redirectTo(string $routeName){
        return $this->redirect( $this->urlFor($routeName) );
    }
}

Router::initCore(); //Initialize the core wildcards