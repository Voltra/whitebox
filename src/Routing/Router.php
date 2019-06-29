<?php
/////////////////////////////////////////////////////////////////////////
//Namespace
/////////////////////////////////////////////////////////////////////////
namespace WhiteBox\Routing;



/////////////////////////////////////////////////////////////////////////
//Imports
/////////////////////////////////////////////////////////////////////////
use GuzzleHttp\Psr7\ServerRequest;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use WhiteBox\Helpers\RegexHandler;
use WhiteBox\Http\HttpRedirectType;
use WhiteBox\Routing\Abstractions\A_CisRouter;
use WhiteBox\Routing\Abstractions\T_MetaRouter;
use WhiteBox\Routing\Abstractions\T_NamedRedirectionManager;
use WhiteBox\Routing\Abstractions\T_WildcardBasedArrayRouteManager;
use WhiteBox\Routing\Route;



/**A class representing a router in a routing system
 * Class Router
 * @package WhiteBox\Routing
 */
class Router {
    /////////////////////////////////////////////////////////////////////////
    //Traits used
    /////////////////////////////////////////////////////////////////////////
    use T_WildcardBasedArrayRouteManager{
        T_WildcardBasedArrayRouteManager::__construct as protected WBARM__construct;
    }

    use T_MetaRouter{
        T_MetaRouter::__construct as protected MetaRouter__construct;
        T_MetaRouter::urlFor insteadof T_WildcardBasedArrayRouteManager;
    }

    use T_NamedRedirectionManager;



    /////////////////////////////////////////////////////////////////////////
    //Magics
    /////////////////////////////////////////////////////////////////////////
    public function __construct() {
        $this->WBARM__construct();
        $this->MetaRouter__construct();
    }



    /////////////////////////////////////////////////////////////////////////
    //Overrides
    /////////////////////////////////////////////////////////////////////////
    /**Determines whether or not this Router has a given Route (from a method and a regex)
     * @param string $method
     * @param string $re
     * @return bool
     */
    protected function hasRoute(string $method, string $re): bool {
        $paramRoute = new Route($method, $re);
        $routes = $this->getAllRoutes();
        foreach ($routes as $route) {
            if ($route instanceof Route) {
                if ($route->equals($paramRoute))
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
    protected function getRoute(string $method, string $re): ?Route {
        $paramRoute = new Route($method, $re);
        $routes = array_merge($this->routes, $this->getAllTransformedRoutesForMethod($method));
        foreach ($routes as $route) {
            if ($route instanceof Route) {
                if ($route->equals($paramRoute))
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
        if (in_array($method, Route::METHODS, true)) {
            $routes = array_merge($this->routes, $this->getAllTransformedRoutesForMethod($method));
            return array_filter($routes, function (Route $route) use ($method) {
                return $route->method() === $method;
            });
        } else
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

    /**Sets up a Route in this Router
     * @param string $method being the Route's method
     * @param string $re being the Route's regular expression
     * @param callable $functor being the Route's handler
     * @param callable|null $authMiddleware being the Route's middleware
     * @return Route
     */
    protected function setupRoute(string $method, string $re, callable $functor, ?callable $authMiddleware = null): Route {
        if ($this->hasRoute($method, $re)) {
            $route = $this->getRoute($method, $re);
            if ($route !== null) {
                $route->setHandler($functor);

                if ($authMiddleware !== null)
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

        if ($authMiddleware !== null)
            $route->setAuthMiddleware($authMiddleware);

        return $route;
    }


    /**Bootstraps this Router to handle requests
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @return mixed
     */
    protected function handleRequest(ServerRequestInterface $request, ResponseInterface $response) : ResponseInterface{
        $method = $request->getMethod();
        $routes = $this->getRoutesForMethod($method);
        $uri = $request->getUri()->getPath();

        $trimmed_uri = rtrim($uri, "/"); //Removes the trailing slash if there's one, not if root though
        if ($trimmed_uri === "")//If the trimmed string is empty, it was the root that was requested
            $trimmed_uri = "/"; //Then replace it with the root

        if ($uri !== $trimmed_uri) //If the trimmed version is different, then permanent redirect
            return $this->redirect($trimmed_uri, $response, HttpRedirectType::PERMANENT());

        $route = array_reduce($routes, static function ($acc, Route $route) use ($uri) {
            $regex = new RegexHandler(self::makeRegex($route->regex()));
            if ($regex->appliesTo($uri))
                $acc = $route;

            return $acc;
        }, null);

        if ($route === null){
            $response = $response->withStatus(404);
            $route = $this->getRoute("error", "404"); //One must always be defined
        }else if (!call_user_func($route->getAuthMiddleware(), $request)) {
            $response = $response->withStatus(404);
            $route = $this->getRoute("error", "404");
        }


        $arguments = self::uriParams($uri, $route->regex());
        $arguments = array_merge($arguments, [
            $request,
            $response
        ]);


        $response->getBody()->write( call_user_func_array($route->getHandler(), $arguments) );
        return $response;
    }


    /**
     * @param A_CisRouter $subrouter
     * @return $this
     */
    public function register(A_CisRouter $subrouter) {
        if (!in_array($subrouter, $this->subrouters))
            $this->subrouters[] = $subrouter;
        return $this;
    }


    protected function getAllTransformedRoutesForMethod(string $method): array {
        return array_filter($this->getAllTransformedRoutes(), static function (Route $route) use ($method) {
            return $route->method() === $method;
        });
    }

    protected function getAllTransformedRoutes(): array {
        return array_reduce($this->subrouters, static function (array $tRoutes, A_CisRouter $subrouter) {
            return array_merge($tRoutes, array_map(static function (Route $route) use ($subrouter) {
                $r = new Route($route->method(), $subrouter->getPrefix() . rtrim($route->regex(), "/"), $route->getHandler());
                $r->setAuthMiddleware(static function() use($route, $subrouter){
                    return call_user_func_array($route->getAuthMiddleware(),[])
                        && call_user_func_array($subrouter->getDefaultAuthMiddleware(), []);
                });
                return $r;
            }, $subrouter->getRoutes()));
        }, []);
    }

    protected function getAllRoutes() : array{
        return array_merge($this->routes, $this->getAllTransformedRoutes());
    }
}

Router::initCoreWildcards(); //Initialize the core wildcards