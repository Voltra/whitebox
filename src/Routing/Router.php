<?php
namespace WhiteBox\Routing;

use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ServerRequest;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use WhiteBox\Helpers\RegexHandler;
use WhiteBox\Http\AdvancedRequest;
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
    public static function initCore(): void{
        if(!self::$isInitialized) {
            self::$coreWildcards = array_keys(self::$wildcards);
            self::$isInitialized = true;
        }
    }

    /**Registers a wildcard (only if it doesn't exist)
     * @param string $wildcard being the wildcard identifier/non-compiled regex (eg. "/:wildcard/")
     * @param string $regex being the compiled regex for the wildcard
     */
    public static function registerWildcard(string $wildcard, string $regex): void{
        if(!array_key_exists($wildcard, self::$wildcards))
            self::$wildcards[$wildcard] = $regex;
    }

    /**Removes a (non core) wildcard
     * @param string $wildcard being the wildcard identifier/non-compiled regex of the wildcard to remove
     */
    public static function removeWildcard(string $wildcard): void{
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
    public static function registerAliasWildcard(string $alias, string $current): void{
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
    protected static function masksToRegex(string $uri_regex): string{
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
    public static function uriParams(string $uri, string $uri_regex): array{
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
    protected function hasRoute(string $method, string $re): bool{
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
    protected function getRoute(string $method, string $re): ?Route{
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
    protected function getRoutesForMethod(string $method): array{
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
     * @return Route
     */
    protected function setupRoute(string $method, string $re, callable $functor, callable $authMiddleware = null): Route{
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
    public function error404(callable $functor): Route{
        return $this->setupRoute("error", "404", $functor);
    }

    /** Replaces the handler for the given route when accessed via the GET method
     * @param string $route - a string designating the complete route (including the website root, eg. "/user")
     * @param callable $functor - a callable object/function to be called when the route is requested
     *
     * @param callable|null $authMiddleware
     * @return \WhiteBox\Routing\Route
     */
    public function get(string $route, callable $functor, callable $authMiddleware = null): Route{
        return $this->setupRoute("GET", $route, $functor, $authMiddleware);
    }

    /** Replaces the handler for the given route when accessed via the POST method
     * @param string $route - a string designating the complete route (including the website root, eg. "/user")
     * @param callable $functor - a callable object/function to be called when the route is requested
     *
     * @param callable|null $authMiddleware
     * @return \WhiteBox\Routing\Route
     */
    public function post(string $route, callable $functor, callable $authMiddleware = null): Route{
        return $this->setupRoute("POST", $route, $functor, $authMiddleware);
    }

    /** Replaces the handler for the given route when accessed via the PUT method
     * @param string $route - a string designating the complete route (including the website root, eg. "/user")
     * @param callable $functor - a callable object/function to be called when the route is requested
     *
     * @param callable|null $authMiddleware
     * @return \WhiteBox\Routing\Route
     */
    public function put(string $route, callable $functor, callable $authMiddleware = null): Route{
        return $this->setupRoute("PUT", $route, $functor, $authMiddleware);
    }

    /** Replaces the handler for the given route when accessed via the HEAD method
     * @param string $route - a string designating the complete route (including the website root, eg. "/user")
     * @param callable $functor - a callable object/function to be called when the route is requested
     *
     * @param callable|null $authMiddleware
     * @return \WhiteBox\Routing\Route
     */
    public function head(string $route, callable $functor, callable $authMiddleware = null): Route{
        return $this->setupRoute("HEAD", $route, $functor, $authMiddleware);
    }


    /**Bootstraps this Router to handle requests
     * @param ServerRequestInterface $request
     * @return mixed
     */
    protected function handleRequest(ServerRequestInterface $request){
        $request = AdvancedRequest::fromGlobals();
        $method = $request->getMethod();
        $routes = $this->getRoutesForMethod($method);
        $uri = $request->requestURI();

        $trimmed_uri = rtrim($uri, "/"); //Removes the trailing slash if there's one, not if root though
        if($trimmed_uri == "")//If the trimmed string is empty, it was the root that was requested
            $trimmed_uri = "/"; //Then replace it with the root

        if($uri != $trimmed_uri) //If the trimmed version is different, then permanent redirect
            return $this->redirect($trimmed_uri, 301);

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


        return call_user_func_array($route->getHandler(), $arguments);
    }

    /**Runs this Router
     * @param ServerRequestInterface|null $request
     */
    public function run(ServerRequestInterface $request = null){
        if(is_null($request))
            $request = ServerRequest::fromGlobals();

        $this->handleRequest($request);
    }

    /**Retrieves the regex/URL associated to the Route that has the given routeName
     * @param string $routeName being the name of the Route to lookup the url for
     * @return string
     */
    public function urlFor(string $routeName): string{
        $name = "{$routeName}";

        foreach($this->routes as $route){
            if($route->getName() === $name)
                return $route->regex();
        }

        return "";
    }

    /**Redirects to a given URL
     * @param string $url being the URL to redirect to
     * @param int $status
     */
    public function redirect(string $url, int $status = 302): void{
        header("Location: {$url}", true, $status);
    }

    /**Redirects to the URL of the Route that has the given routeName
     * @param string $routeName being the name of the Route to redirect to
     */
    public function redirectTo(string $routeName): void{
        $this->redirect( $this->urlFor($routeName) );
    }
}

Router::initCore(); //Initialize the core wildcards