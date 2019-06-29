<?php
/////////////////////////////////////////////////////////////////////////
//Namespace
/////////////////////////////////////////////////////////////////////////
namespace WhiteBox\Routing\Abstractions;



/////////////////////////////////////////////////////////////////////////
//Imports
/////////////////////////////////////////////////////////////////////////
use WhiteBox\Routing\Abstractions\T_WildcardBasedRouteManager;
use WhiteBox\Routing\Abstractions\T_RouteDispatcher;
use WhiteBox\Routing\Route;
use WhiteBox\Routing\SubRouter;



/**An abstract class representing a router that can attach cisrouters (subrouters) to it
 * Class T_MetaRouter
 * @package WhiteBox\Routing\Abstractions
 */
trait T_MetaRouter /*extends T_WildcardBasedArrayRouteManager*/ {
    /////////////////////////////////////////////////////////////////////////
    //Traits used
    /////////////////////////////////////////////////////////////////////////
    use T_RouteDispatcher;



    /////////////////////////////////////////////////////////////////////////
    //Properties
    /////////////////////////////////////////////////////////////////////////
    /**
     * @var T_CisRouter[]
     */
    protected $subrouters;



    /////////////////////////////////////////////////////////////////////////
    //Magics
    /////////////////////////////////////////////////////////////////////////
    public function __construct() {
        //parent::__construct();
        $this->subrouters = [];
    }



    /////////////////////////////////////////////////////////////////////////
    //Methods
    /////////////////////////////////////////////////////////////////////////
    /**Register a subrouter in this metarouter
     * @param A_CisRouter $subrouter being the cisrouter(subrouter) to register in this metarouter
     * @return $this
     */
    public abstract function register(A_CisRouter $subrouter);

    /**
     * @param string $method
     * @return array
     */
    protected abstract function getAllTransformedRoutesForMethod(string $method) : array;

	/**
	 * @return Route[]
	 */
    protected abstract function getAllRoutes() : array;

    protected abstract function getAllTransformedRoutes() : array;


    public function urlFor(string $routeName, ?array $uriParams = null): string {
        $name = (string)$routeName;

        foreach ($this->getAllRoutes() as $route) {
            if ($route->getName() === $name) {
                if($uriParams === null)
                    return $route->regex();
                else {
                    $uriKeys = array_keys($uriParams);
                    $uriValues = array_values($uriParams);
                    $uriParameters = array_map(static function($key, $value){
                        return [
                            "key" => $key,
                            "value" => $value
                        ];
                    }, $uriKeys, $uriValues);

                    return array_reduce($uriParameters, static function(string $routeBuilt, array $uriParam): string {
                        $key = (string)$uriParam["key"];
                        $value = (string)$uriParam["value"];
                        return preg_replace("/:{$key}/", $value, $routeBuilt, 1);
                    }, $route->regex());
                }
            }
        }

        return "";
    }
}