<?php
/////////////////////////////////////////////////////////////////////////
//Namespace
/////////////////////////////////////////////////////////////////////////
namespace WhiteBox;



/////////////////////////////////////////////////////////////////////////
//Imports
/////////////////////////////////////////////////////////////////////////
use DI\DependencyException;
use Doctrine\Common\Annotations\AnnotationException;
use Doctrine\Common\Annotations\AnnotationRegistry;
use InvalidArgumentException;
use Noodlehaus\AbstractConfig;
use ReflectionException;
use WhiteBox\Helpers\ArrayHelper;
use WhiteBox\Middlewares\T_MiddlewareHub;
use WhiteBox\Rendering\Engine\PhpHtmlRenderEngine;
use WhiteBox\Rendering\I_ViewRenderEngine;
use WhiteBox\Routing\Abstractions\A_CisRouter;
use WhiteBox\Routing\Abstractions\T_MetaRouter;
use WhiteBox\Routing\Abstractions\T_NamedRedirectionManager;
use WhiteBox\Routing\Abstractions\T_RouteBuilder;
use WhiteBox\Routing\Abstractions\T_WildcardBasedRouteSystem;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use WhiteBox\Routing\Route;
use WhiteBox\Routing\Router;
use DI\Container;
use DI\ContainerBuilder;
use WhiteBox\Http\HttpRedirectType;
use WhiteBox\Routing\Controllers\Annotations\DefineRoute;
use WhiteBox\Routing\Controllers\Annotations\DefineSubRouter;
use WhiteBox\Routing\Controllers\Annotations\Get;
use WhiteBox\Routing\Controllers\Annotations\Post;
use ReflectionClass;


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
    use T_NamedRedirectionManager;
    use T_WildcardBasedRouteSystem;



    /////////////////////////////////////////////////////////////////////////
    //Properties
    /////////////////////////////////////////////////////////////////////////
    /**
     * @var Router
     */
    protected $router;

    /**
     * @var Container
     */
    public $container;



    /////////////////////////////////////////////////////////////////////////
    //Magics
    /////////////////////////////////////////////////////////////////////////
    /**
     * App constructor.
     */
    public function __construct() {
        $this->MetaRouter__construct();
        $this->MiddlewareHub__construct();

        $this->router = new Router();
        $this->loadConfig(static::getDefaultDependencyInjectionDefinitions());

        $this->bootstrapAnnotations();
    }

    /**
     * @param string $name
     * @return mixed
     * @throws DependencyException
     * @throws \DI\NotFoundException
     */
    public function __get(string $name){
        return $this->container->get($name);
    }

    public function __isset(string $name){
        return $this->container->has($name);
    }

    public function __set(string $name, $value){
		return $this->container->set($name, $value);
	}

	public function has(string $name): bool {
        return $this->__isset($name);
    }



    /////////////////////////////////////////////////////////////////////////
    //Methods
    /////////////////////////////////////////////////////////////////////////
    protected function bootstrapAnnotations(){
        AnnotationRegistry::registerLoader("class_exists");//TODO: Watch for deprectation

        $annotationClasses = [
            DefineRoute::class,
            DefineSubRouter::class,
            Get::class,
            Post::class
        ];

        array_walk($annotationClasses, static function(string $annotationClass){
            if(!AnnotationRegistry::loadAnnotationClass($annotationClass))
                throw new AnnotationException("Couldn't load annotation from: {$annotationClass}");
        });

        return $this;
    }

    public function loadConfig(array $config, bool $autoWiring=true, bool $useAnnotations=false){
        if(!ArrayHelper::is_assoc($config))
            throw new InvalidArgumentException("The configuration array must be an associative array");

        $definitions = array_merge(static::getDefaultDependencyInjectionDefinitions(), $config);

        $this->container = (new ContainerBuilder())
        ->addDefinitions($definitions)
        ->useAutowiring($autoWiring)
        ->useAnnotations($useAnnotations)
        ->build();

        return $this;
    }



    /////////////////////////////////////////////////////////////////////////
    //Class methods
    /////////////////////////////////////////////////////////////////////////
    public static function getDefaultConfig(): AbstractConfig{
        return new AppConfig([]);
    }

    public static function getDefaultDependencyInjectionDefinitions(): array{
        return [
            "config" => static::getDefaultConfig(),
            "view" => \DI\object(PhpHtmlRenderEngine::class),
            I_ViewRenderEngine::class => \DI\get("view")
        ];
    }



    /////////////////////////////////////////////////////////////////////////
    //Overrides
    /////////////////////////////////////////////////////////////////////////
    public function redirect(string $url, ResponseInterface $res, ?HttpRedirectType $status = null) : ResponseInterface{
        return $this->router->redirect($url, $res, $status);
    }

    public function redirectTo(string $routeName, ResponseInterface $res, ?HttpRedirectType $status = null) : ResponseInterface{
        return $this->router->redirectTo($routeName, $res, $status);
    }

    public function urlFor(string $routeName, ?array $uriParams=null) : string{
        return $this->router->urlFor($routeName, $uriParams);
    }

    /**Register a subrouter in this metarouter
     * @param A_CisRouter $subrouter being the cisrouter(subrouter) to register in this metarouter
     * @return $this
     */
    public function register(A_CisRouter $subrouter) {
        $this->router->register($subrouter);
        return $this;
    }

	/**
	 * @param string $method
	 * @return array
	 * @throws ReflectionException
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
	 * @param ResponseInterface $response
	 * @return mixed
	 * @throws ReflectionException
	 */
    protected function handleRequest(ServerRequestInterface $request, ResponseInterface $response) : ResponseInterface{
        $handleRequest = (new ReflectionClass(Router::class))
            ->getMethod("handleRequest");

        $handleRequest->setAccessible(true);
        $ret = $handleRequest->invokeArgs($this->router, [$request, $this->process($request, $response)]);
        $handleRequest->setAccessible(false);

        return $ret;
    }

	/**Sets up a Route in this T_RouteBuilder
	 * @param string $method being the Route's method
	 * @param string $re being the Route's regular expression
	 * @param callable $functor being the Route's handler
	 * @param callable|null $authMiddleware being the Route's middleware
	 * @return Route
	 * @throws ReflectionException
	 */
    protected function setupRoute(string $method, string $re, callable $functor, ?callable $authMiddleware = null): Route {
        $setupRoute = (new ReflectionClass(Router::class))
		->getMethod("setupRoute");

        $setupRoute->setAccessible(true);
        $ret = $setupRoute->invokeArgs($this->router, [$method, $re, $functor, $authMiddleware]);
        $setupRoute->setAccessible(false);

        return $ret;
    }

	/**Determines whether or not this T_RouteStores has a given Route (from a method and a regex)
	 * @param string $method being the method to check
	 * @param string $re being the Regex to check
	 * @return bool
	 * @throws ReflectionException
	 */
    protected function hasRoute(string $method, string $re): bool {
        $hasRoute = (new ReflectionClass(Router::class))
		->getMethod("hasRoute");

        $hasRoute->setAccessible(true);
        $ret = $hasRoute->invokeArgs($this->router, [$method, $re]);
        $hasRoute->setAccessible(false);

        return $ret;
    }

	/**Retrieves a Route in this T_RouteStore from a method and a regex
	 * @param string $method being the method of the Route to retrieve
	 * @param string $re being the Regex of the Route to retrieve
	 * @return Route|null
	 * @throws ReflectionException
	 */
    protected function getRoute(string $method, string $re): ?Route {
        $getRoute = (new ReflectionClass(Router::class))
		->getMethod("getRoute");

        $getRoute->setAccessible(true);
        $ret = $getRoute->invokeArgs($this->router, [$method, $re]);
        $getRoute->setAccessible(false);

        return $ret;
    }

	/**Retrieves the array of Route which method's is the given method
	 * @param string $method being the given method
	 * @return Route[]
	 * @throws ReflectionException
	 */
    protected function getRoutesForMethod(string $method): array {
        $getRoutesForMethod = (new ReflectionClass(Router::class))
		->getMethod("getRoutesForMethod");

        $getRoutesForMethod->setAccessible(true);
        $ret = $getRoutesForMethod->invoke($this->router, $method);
        $getRoutesForMethod->setAccessible(false);

        return $ret;
    }

	/**Adds a Route to this T_RouteStore
	 * @param Route $route being the route that is being added to this T_RouteStore
	 * @return $this
	 * @throws ReflectionException
	 */
    protected function addRoute(Route $route) {
        $addRoute = (new ReflectionClass(Router::class))
		->getMethod("addRoute");

        $addRoute->setAccessible(true);
        $ret = $addRoute->invoke($this->router, $route);
        $addRoute->setAccessible(false);

        return $ret;
    }

    /**A static method initializing the core wildcards if they are not initialized
     */
    public static function initCoreWildcards(): void {
        Router::initCoreWildcards();
    }

    /**Registers a wildcard (only if it doesn't exist)
     * @param string $wildcard being the wildcard identifier/non-compiled regex (eg. "/:wildcard/")
     * @param string $regex being the compiled regex for the wildcard
     */
	public static function registerWildcard(string $wildcard, string $regex): void {
        Router::registerWildcard($wildcard, $regex);
    }

    /**Removes a (non core) wildcard
     * @param string $wildcard being the wildcard identifier/non-compiled regex of the wildcard to remove
     */
	public static function removeWildcard(string $wildcard): void {
        Router::removeWildcard($wildcard);
    }

    /**Registers a wildcard as an alias of an already registered wildcard
     * @param string $alias being the wildcard identifier/non-compiled regex of the new wildcard
     * @param string $current being the the wildcard identifier/non-compiled regex of the aliased wildcard
     */
	public static function registerAliasWildcard(string $alias, string $current): void {
        Router::registerAliasWildcard($alias, $current);
    }
}