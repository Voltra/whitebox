<?php
/////////////////////////////////////////////////////////////////////////
//Namespace
/////////////////////////////////////////////////////////////////////////
namespace WhiteBox;



/////////////////////////////////////////////////////////////////////////
//Imports
/////////////////////////////////////////////////////////////////////////
use Psr\Http\Message\ServerRequestInterface;
use ReflectionClass;
use WhiteBox\Http\HttpRedirectType;
use WhiteBox\Middlewares\T_MiddlewareHub;
use WhiteBox\Routing\Abstractions\T_MetaRouter;
use WhiteBox\Routing\Abstractions\T_RouteBuilder;
use WhiteBox\Routing\Route;
use WhiteBox\Routing\Router;
use WhiteBox\Routing\SubRouter;


class App{
    /////////////////////////////////////////////////////////////////////////
    //Traits used
    /////////////////////////////////////////////////////////////////////////
    use T_MetaRouter{
        T_MetaRouter::__construct as protected MetaRouter__construct;
    }
    use T_RouteBuilder;
    use T_MiddlewareHub{
        T_MiddlewareHub::__construct as protected MiddlewareHub__construct;
    }


    public function __construct() {
        $this->MetaRouter__construct();
        $this->MiddlewareHub__construct();
        $this->router = new Router();
    }

    /////////////////////////////////////////////////////////////////////////
    //Properties
    /////////////////////////////////////////////////////////////////////////
    /**
     * @var Router
     */
    protected $router;


    public function redirect(string $url, ?HttpRedirectType $status = null) : bool{
        return $this->router->redirect($url, $status);
    }

    public function redirectTo(string $routeName, ?HttpRedirectType $status = null) : bool{
        return $this->router->redirectTo($routeName);
    }

    public function urlFor(string $routeName, ?array $uriParams=null) : string{
        return $this->router->urlFor($routeName, $uriParams);
    }


    /////////////////////////////////////////////////////////////////////////
    //Overrides
    /////////////////////////////////////////////////////////////////////////
    /**Register a subrouter in this metarouter
     * @param SubRouter $subrouter being the cisrouter(subrouter) to register in this metarouter
     * @return $this
     */
    public function register(SubRouter $subrouter) {
        $this->router->register($subrouter);
        return $this;
    }

    /**
     * @param string $method
     * @return array
     */
    protected function getAllTransformedRoutesForMethod(string $method): array {
        $getAllTransformedRoutesForMethod = (new ReflectionClass(Router::class))
        ->getMethod("getAllTransformedRoutesForMethod");

        $getAllTransformedRoutesForMethod->setAccessible(true);
        $ret = $getAllTransformedRoutesForMethod->invoke($this->router, $method);
        $getAllTransformedRoutesForMethod->setAccessible(false);

        return $ret;
    }

    protected function getAllRoutes(): array {
        $getAllRoutes = (new ReflectionClass(Router::class))
            ->getMethod("getAllRoutes");

        $getAllRoutes->setAccessible(true);
        $ret = $getAllRoutes->invoke($this->router);
        $getAllRoutes->setAccessible(false);

        return $ret;
    }

    protected function getAllTransformedRoutes(): array {
        $getAllTransformedRoutes = (new ReflectionClass(Router::class))
            ->getMethod("getAllTransformedRoutes");

        $getAllTransformedRoutes->setAccessible(true);
        $ret = $getAllTransformedRoutes->invoke($this->router);
        $getAllTransformedRoutes->setAccessible(false);

        return $ret;
    }

    /**The protected way to handle a request
     * @param ServerRequestInterface $request being the request to handle
     * @return mixed
     */
    protected function handleRequest(ServerRequestInterface $request) {
        $this->process($request, $this);
        return $this->router->run($request);
    }

    /**Sets up a Route in this T_RouteBuilder
     * @param string $method being the Route's method
     * @param string $re being the Route's regular expression
     * @param callable $functor being the Route's handler
     * @param callable|null $authMiddleware being the Route's middleware
     * @return Route
     */
    protected function setupRoute(string $method, string $re, callable $functor, ?callable $authMiddleware = null): Route {
        $setupRoute = (new ReflectionClass(Router::class))
            ->getMethod("setupRoute");

        $setupRoute->setAccessible(true);
        $ret = $setupRoute->invokeArgs($this->router, [$method, $re, $functor, $authMiddleware]);
        $setupRoute->setAccessible(false);

        return $ret;
    }
}