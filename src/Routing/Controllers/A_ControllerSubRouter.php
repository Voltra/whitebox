<?php
namespace WhiteBox\Routing\Controllers;


use Doctrine\Common\Annotations\AnnotationException;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\IndexedReader;
use ReflectionClass;
use ReflectionMethod;
use WhiteBox\Routing\Route;
use WhiteBox\Routing\Abstractions\A_CisRouter;
use WhiteBox\Routing\Abstractions\T_NamedRedirectionManager;
use WhiteBox\Routing\Controllers\Annotations\DefineRoute;
use WhiteBox\Routing\Controllers\Annotations\DefineSubRouter;

/**
 * Class A_ControllerSubRouter
 * @package WhiteBox\DefineRoute
 */
abstract class A_ControllerSubRouter extends A_CisRouter{
    use T_NamedRedirectionManager;

    protected $annotationsReader;

    /**
     * A_ControllerSubRouter constructor.
     * @param callable|null $defaultAuthMW
     * @throws AnnotationException
     */
    public function __construct(?callable $defaultAuthMW = null) {
        $this->annotationsReader = new IndexedReader(new AnnotationReader());
        parent::__construct($this->getPrefix(), $defaultAuthMW);
    }

    /**
     * @return string
     * @throws AnnotationException
     */
    public function getPrefix(): string {
        $ThisClass = new ReflectionClass(static::class);
        $defineSubRouterAnnotation = $this->annotationsReader->getClassAnnotation($ThisClass, DefineSubRouter::class);

        if(is_null($defineSubRouterAnnotation))
            throw new AnnotationException("There is no DefineSubRouter annotation found for this controller");

        return $defineSubRouterAnnotation->prefix;
    }

    /**
     * @return array
     */
    public function getRoutes(): array {
        $routeRefMethods = $this->getRouteMethods();
        $definedRoutes = array_map(function(ReflectionMethod $method){
            $annotations = $this->annotationsReader->getMethodAnnotations($method);
            foreach($annotations as $annotation){
                if($annotation instanceof DefineRoute)
                    return ["annotation"=>$annotation, "methodName"=>$method->getName()];
            }
            return null;
        }, $routeRefMethods);
        $definedRoutes = array_filter($definedRoutes, function($elem){
            return !is_null($elem);
        });

        return array_map(function(array $routing): Route{
            /**
             * @var DefineRoute
             */
            $annotation = $routing["annotation"];
            $methodName = $routing["methodName"];

            return (new Route($annotation->method, $annotation->uri, [$this, $methodName], $this->getDefaultAuthMiddleware()))
            ->name($annotation->name);
        }, $definedRoutes);
    }

    /**
     * @return ReflectionMethod[]
     */
    protected function getRouteMethods(): array{
        $ThisClass = new ReflectionClass(static::class);
        $publics = $ThisClass->getMethods(ReflectionMethod::IS_PUBLIC);
        return array_filter($publics, function(ReflectionMethod $method){
            $annotations = $this->annotationsReader->getMethodAnnotations($method);
            return array_reduce($annotations, function(bool $acc, $annotation){
                return $acc || ($annotation instanceof DefineRoute);
            }, false);
        });
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

    protected function addRoute(Route $route) {
        $this->routes[] = $route;
    }

    /**
     * @param string $method
     * @return array
     */
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
}