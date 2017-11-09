<?php
namespace WhiteBox\Routing;
use WhiteBox\Routing\Route;


/**A trait used to represent a route loader
 * Trait TRouteLoader
 * @package WhiteBox\Routing
 */
trait T_RouteLoader{
    /////////////////////////////////////////////////////////////////////////
    //Properties
    /////////////////////////////////////////////////////////////////////////
    /**The path (up to the containing folder) that holds all the route files
     * @var string
     */
    protected $path;



    /////////////////////////////////////////////////////////////////////////
    //Magics
    /////////////////////////////////////////////////////////////////////////
    /**Instantiate the TRouteLoader
     * TRouteLoader constructor.
     * @param string $path being the path to the route files
     */
    public function __construct(string $path){
        $this->path = $path;
    }



    /////////////////////////////////////////////////////////////////////////
    //Methods
    /////////////////////////////////////////////////////////////////////////
    /**Loads all the routes located in the path
     * @param Router $router being the Router to add the routes to
     * @return void
     */
    public abstract function loadRoutes(Router $router): void;

    /**
     * @param string $fileURI
     * @return string
     */
    public abstract function generateLoaderFile(string $fileURI): string;
}