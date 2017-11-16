<?php
/////////////////////////////////////////////////////////////////////////
//Namespace
/////////////////////////////////////////////////////////////////////////
namespace WhiteBox\Routing\Abstractions;



/////////////////////////////////////////////////////////////////////////
//Imports
/////////////////////////////////////////////////////////////////////////
use WhiteBox\Routing\Abstractions\A_WildcardBasedRouteManager;
use WhiteBox\Routing\Abstractions\T_RouteDispatcher;
use WhiteBox\Routing\Route;


/**An abstract class representing a router that can attach cisrouters (subrouters) to it
 * Class A_MetaRouter
 * @package WhiteBox\Routing\Abstractions
 */
abstract class A_MetaRouter extends A_WildcardBasedArrayRouteManager {
    /////////////////////////////////////////////////////////////////////////
    //Traits used
    /////////////////////////////////////////////////////////////////////////
    use T_RouteDispatcher;



    /////////////////////////////////////////////////////////////////////////
    //Properties
    /////////////////////////////////////////////////////////////////////////
    /**
     * @var A_CisRouter[]
     */
    protected $subrouters;



    /////////////////////////////////////////////////////////////////////////
    //Magics
    /////////////////////////////////////////////////////////////////////////
    public function __construct() {
        parent::__construct();
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

    protected abstract function getAllRoutes() : array;

    protected abstract function getAllTransformedRoutes() : array;
}