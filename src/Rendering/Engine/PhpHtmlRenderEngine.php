<?php
/////////////////////////////////////////////////////////////////////////
//Namespace
/////////////////////////////////////////////////////////////////////////
namespace WhiteBox\Rendering\Engine;



/////////////////////////////////////////////////////////////////////////
//Imports
/////////////////////////////////////////////////////////////////////////
use Psr\Http\Message\ResponseInterface;
use WhiteBox\Helpers\MagicalArray;
use WhiteBox\Http\Session;
use WhiteBox\Rendering\I_ViewRenderEngine;
use WhiteBox\Routing\Router;


$vd = new MagicalArray(); //View's data
//first initialization on creation

/** A view render "engine" that uses PHP's vanilla syntax
 * Class PhpHtmlRenderEngine
 * @package WhiteBox\Rendering\Engine
 */
class PhpHtmlRenderEngine implements I_ViewRenderEngine {
    /////////////////////////////////////////////////////////////////////////
    //Methods
    /////////////////////////////////////////////////////////////////////////
    /** Renders a view to a HTML string from the given URI
     * @param ResponseInterface $res
     * @param string $uri being the uri to the view file (must be a PHP file)
     * @param array $data being the data that will be passed to the view
     * @return string
     */
    public function render(ResponseInterface $res, string $uri, array $data = []): string{
        Session::set("VIEW_DATA", $data);
        self::beginViewRendering();
        ob_start();
        global $vd;
        require "{$uri}";
        self::endViewRendering();
        return ob_get_clean();
    }



    /////////////////////////////////////////////////////////////////////////
    //Class methods
    /////////////////////////////////////////////////////////////////////////
    /**A static method that setup the view rendering
     */
    protected static function beginViewRendering(): void{
        if(Session::isStarted() && !is_null(Session::get("VIEW_DATA")))
            self::setViewData( Session::get("VIEW_DATA") );
        else
            self::resetViewData();
    }

    /**A static method that resets the engine after rendering
     */
    protected static function endViewRendering(): void{
        if(Session::isStarted()) {
            self::resetViewData();
            Session::set("VIEW_DATA", []);
        }
    }

    /**A helper method to reset the view's data global variable
     */
    protected static function resetViewData(): void{
        self::setViewData([]);
    }

    /**A helper method to set the view's data global variable
     * @param array $data being an associative array containing all of the view's new data
     */
    protected static function setViewData(array $data): void{
        global $vd;
        $vd = new MagicalArray($data);
    }


    protected $router;
    public function __construct(?Router $router=null) {
        $this->router = $router;
    }

    public function urlFor(string $routeName, ?array $uriParams=null): string{
        if(is_null($this->router))
            return "/";
        else
            return $this->router->urlFor($routeName, $uriParams);
    }
}