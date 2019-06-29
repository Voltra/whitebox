<?php
/////////////////////////////////////////////////////////////////////////
//Namespace
/////////////////////////////////////////////////////////////////////////
namespace WhiteBox\Routing\Controllers;



/////////////////////////////////////////////////////////////////////////
//Imports
/////////////////////////////////////////////////////////////////////////
use Doctrine\Common\Annotations\AnnotationException;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\IndexedReader;
use ReflectionClass;
use ReflectionMethod;
use WhiteBox\Rendering\I_ViewRenderEngine;
use WhiteBox\Routing\Route;
use WhiteBox\Routing\Abstractions\A_CisRouter;
use WhiteBox\Routing\Abstractions\T_NamedRedirectionManager;
use WhiteBox\Routing\Controllers\Annotations\DefineRoute;
use WhiteBox\Routing\Controllers\Annotations\DefineSubRouter;



/**A class that represents the shared behavior of controllers as subrouters
 * Class A_ControllerSubRouter
 * @package WhiteBox\DefineRoute
 */
abstract class A_ControllerSubRouter extends A_CisRouter{
    /////////////////////////////////////////////////////////////////////////
    //Traits used
    /////////////////////////////////////////////////////////////////////////
    use T_NamedRedirectionManager;



    /////////////////////////////////////////////////////////////////////////
    //Properties
    /////////////////////////////////////////////////////////////////////////
    protected $annotationsReader;
    protected $view;



    /////////////////////////////////////////////////////////////////////////
    //Magics
    /////////////////////////////////////////////////////////////////////////
    /**
     * A_ControllerSubRouter constructor.
     * @param I_ViewRenderEngine $view
     * @param callable|null $defaultAuthMW
     * @throws AnnotationException
     */
    public function __construct(I_ViewRenderEngine $view, ?callable $defaultAuthMW = null) {
        $this->annotationsReader = new IndexedReader(new AnnotationReader());
        $this->view = $view;
        parent::__construct($this->getPrefix(), $defaultAuthMW);
    }



    /////////////////////////////////////////////////////////////////////////
    //Methods
    /////////////////////////////////////////////////////////////////////////
	/**Retrieves the prefix for this controller
	 * @return string
	 * @throws AnnotationException
	 * @throws \ReflectionException
	 */
    public function getPrefix(): string {
        $ThisClass = new ReflectionClass(static::class);
        $defineSubRouterAnnotation = $this->annotationsReader->getClassAnnotation($ThisClass, DefineSubRouter::class);

        if($defineSubRouterAnnotation === null)
            throw new AnnotationException("There is no DefineSubRouter annotation found for this controller");

        return $defineSubRouterAnnotation->prefix;
    }

	/**Returns the methods of this controller that are defined as routes (as reflection methods)
	 * @return ReflectionMethod[]
	 * @throws \ReflectionException
	 */
    protected function getRouteMethods(): array{
        $ThisClass = new ReflectionClass(static::class);
        $publics = $ThisClass->getMethods(ReflectionMethod::IS_PUBLIC);
        return array_filter($publics, function(ReflectionMethod $method){
            $annotations = $this->annotationsReader->getMethodAnnotations($method);
            return array_reduce($annotations, static function(bool $acc, $annotation){
                return $acc || ($annotation instanceof DefineRoute);
            }, false);
        });
    }



    /////////////////////////////////////////////////////////////////////////
    //Overrides
    /////////////////////////////////////////////////////////////////////////
	/**
	 * @return array
	 * @throws \ReflectionException
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
            return $elem !== null;
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
	 * @throws \ReflectionException
	 */
    protected  function getRoutesForMethod(string $method): array {
        return array_filter($this->getRoutes(), static function(Route $route) use($method){
            return $route->method() === $method;
        });
    }

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
}