<?php
/////////////////////////////////////////////////////////////////////////
//Namespace
/////////////////////////////////////////////////////////////////////////
namespace WhiteBox\Routing;



/////////////////////////////////////////////////////////////////////////
//Imports
/////////////////////////////////////////////////////////////////////////
use GuzzleHttp\Psr7\ServerRequest;
use PHPUnit\Runner\Exception;
use Psr\Http\Message\ServerRequestInterface;
use WhiteBox\Helpers\RegexHandler;
use WhiteBox\Http\HttpRedirectType;
use WhiteBox\Routing\Abstractions\A_CisRouter;
use WhiteBox\Routing\Abstractions\A_MetaRouter;
use WhiteBox\Routing\Route;



/**A class representing a router in a routing system
 * Class Router
 * @package WhiteBox\Routing
 */
class Router extends A_MetaRouter {
    /////////////////////////////////////////////////////////////////////////
    //Methods
    /////////////////////////////////////////////////////////////////////////
    /**Retrieves the regex/URL associated to the Route that has the given routeName
     * @param string $routeName being the name of the Route to lookup the url for
     * @return string
     */
    public function urlFor(string $routeName): string {
        $name = "{$routeName}";

        foreach ($this->routes as $route) {
            if ($route->getName() === $name)
                return $route->regex();
        }

        return "";
    }

    /**Redirects to a given URL
     * @param string $url being the URL to redirect to
     * @param HttpRedirectType $status
     */
    public function redirect(string $url, ?HttpRedirectType $status = null): void {
        if (is_null($status))
            $status = HttpRedirectType::FOUND();

        header("Location: {$url}", true, $status->getCode());
    }

    /**Redirects to the URL of the Route that has the given routeName
     * @param string $routeName being the name of the Route to redirect to
     */
    public function redirectTo(string $routeName): void {
        $this->redirect($this->urlFor($routeName));
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


    /**Bootstraps this Router to handle requests
     * @param ServerRequestInterface $request
     * @return mixed
     */
    protected function handleRequest(ServerRequestInterface $request) {
        $method = $request->getMethod();
        $routes = $this->getRoutesForMethod($method);
        $uri = $request->getUri()->getPath();

        $trimmed_uri = rtrim($uri, "/"); //Removes the trailing slash if there's one, not if root though
        if ($trimmed_uri == "")//If the trimmed string is empty, it was the root that was requested
            $trimmed_uri = "/"; //Then replace it with the root

        if ($uri != $trimmed_uri) //If the trimmed version is different, then permanent redirect
            $this->redirect($trimmed_uri, HttpRedirectType::PERMANENT()) and die();

        $route = array_reduce($routes, function ($acc, Route $route) use ($uri) {
            $regex = new RegexHandler(self::makeRegex($route->regex()));
            if ($regex->appliesTo($uri))
                $acc = $route;

            return $acc;
        }, null);

        if (is_null($route))
            $route = $this->getRoute("error", "404"); //One must always be defined
        else if (!call_user_func($route->getAuthMiddleware(), $request))
            $route = $this->getRoute("error", "404");


        $arguments = self::uriParams($uri, $route->regex());
        $arguments = array_merge($arguments, [
            $request
        ]);


        return call_user_func_array($route->getHandler(), $arguments);
    }

    /**Runs this Router
     * @param ServerRequestInterface|null $request
     * @return mixed|void
     */
    public function run(?ServerRequestInterface $request = null) {
        if (is_null($request))
            $request = ServerRequest::fromGlobals();

        $this->handleRequest($request);
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


    public function getAllTransformedRoutesForMethod(string $method): array {
        return array_filter($this->getAllTransformedRoutes(), function (Route $route) use ($method) {
            return $route->method() === $method;
        });
    }

    protected function getAllTransformedRoutes(): array {
        return array_reduce($this->subrouters, function (array $tRoutes, A_CisRouter $subrouter) {
            return array_merge($tRoutes, array_map(function (Route $route) use ($subrouter) {
                $r = new Route($route->method(), $subrouter->getPrefix() . rtrim($route->regex(), "/"), $route->getHandler());
                $r->setAuthMiddleware(function() use($route, $subrouter){
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