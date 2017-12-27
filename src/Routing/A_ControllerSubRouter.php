<?php
namespace WhiteBox\Routing;


use ReflectionClass;
use ReflectionMethod;
use WhiteBox\Helpers\RegexHandler;
use WhiteBox\Routing\Abstractions\A_CisRouter;
use WhiteBox\Routing\Abstractions\T_NamedRedirectionManager;

abstract class A_ControllerSubRouter extends A_CisRouter{
    use T_NamedRedirectionManager;

    public function __construct(?string $prefix=null, ?callable $defaultAuthMW = null) {
        if(is_null($prefix))
            $prefix = "/".static::getControllerName();

        parent::__construct($prefix, $defaultAuthMW);
    }

    public function getRoutes(): array {
        $ThisClass = new ReflectionClass(static::class);
        $publicMethods = $ThisClass->getMethods(ReflectionMethod::IS_PUBLIC);
        return array_map(function(ReflectionMethod $method): Route{
            $methodName = $method->getName();
            $httpMethod = static::getMethod($methodName) ?? "error";
            $routeRe = "/" . (static::getName($methodName) ?? "null");

            return (new Route($httpMethod, $routeRe, [$this, $methodName], $this->getDefaultAuthMiddleware()))
            ->name(static::getFullRouteName($methodName));
        }, $publicMethods);
    }

    protected function hasRoute(string $method, string $re): bool {
        $paramRoute = new Route($method, $re);
        foreach($this->getRoutes() as $route){
            if($paramRoute->equals($route))
                return true;
        }
        return false;
    }

    protected function getRoute(string $method, string $re): ?Route {
        $paramRoute = new Route($method, $re);
        foreach($this->getRoutes() as $route){
            if($paramRoute->equals($route))
                return $route;
        }
        return null;
    }

    protected  function addRoute(Route $route) {
        $this->routes[] = $route;
    }

    protected  function getRoutesForMethod(string $method): array {
        return array_filter($this->getRoutes(), function(Route $route) use($method){
            return $route->method() === $method;
        });
    }

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

    public static function getNonRoutes(): array{
        $CisRouter = new ReflectionClass(parent::class);
        $pubs = $CisRouter->getMethods(ReflectionMethod::IS_PUBLIC);
        return array_map(function(ReflectionMethod $method): string{
            return $method->getName();
        }, $pubs);
    }

    public static function getMethod(string $m): ?string{
        $re = new RegexHandler("/^(error|GET|POST|PUT|HEAD)_/");
        if($re->appliesTo($m)){
            $groups = $re->getGroups($m);
            if(count($groups) > 0)
                return $groups[0];
        }

        return null;
    }

    public static function getName(string $m): ?string{
        $re = new RegexHandler("/^(?:error|GET|PUT|HEAD)_(.+)$/");
        if($re->appliesTo($m)){
            $groups = $re->getGroups($m);
            if(count($groups) > 0)
                return $groups[0];
        }

        return null;
    }

    public static function getControllerName(): string{
        $ThisClass = new ReflectionClass(static::class);
        $name = strtolower($ThisClass->getName());
        $name = str_replace("controller", "", $name);
        return $name;
    }

    public static function getFullRouteName(string $m): string{
        $class = static::getControllerName();
        $method = static::getMethod($m) ?? "error";
        $method = strtolower($method);
        $name = static::getName($m) ?? "null";

        return "{$class}.{$method}.{$name}";
    }


    ///
    /// A controller's name's format:
    /// class ApiController extends A_ControllerSubRouter --> api
    ///

    ///
    /// A method's name's format:
    /// public function GET_index
    /// public function POST_article
    /// public function error_404
    ///

    ///
    /// A route's name's format:
    /// ApiController::GET_index --> api.get.index
    /// ApiController::POST_article --> api.post.article
    /// ApiController::error_404 --> api.error.404
    ///
}