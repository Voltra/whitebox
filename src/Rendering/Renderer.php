<?php
namespace WhiteBox\Rendering;

use Error;
use WhiteBox\Helpers\TraitChecker;
use WhiteBox\Http\Session;
use WhiteBox\Rendering\I_ViewRenderEngine;
use WhiteBox\Rendering\T_ViewRenderEngine;

class Renderer{
    protected static $engine = null;
    protected static $baseLocation = "";

    /**Checks whether or not there's a render engine available
     * @return bool
     */
    public static function hasRenderEngine(){
        return !is_null(self::$engine);
    }

    /**Checks whether or not there's a render engine available, if there's one it also checks if it is valid (has a "render" method)
     * @return bool
     */
    public static function hasValidRenderEngine(){
        return self::hasRenderEngine()  //Has an engine
            && ( //and
                self::$engine instanceof I_ViewRenderEngine //is a I_ViewRenderEngine
                || (new TraitChecker(get_class(self::$engine)))->hasTrait(T_ViewRenderEngine::class) //or uses TViewRenderEngine trait
                || ( method_exists(self::$engine, "render") && is_callable([self::$engine, "render"]) ) //or has a method render
            );
    }

    /**Renders a view file (w/ a render engine if one is registered)
     * @param string $uri - the URI/URL to the view file to render
     * @param array $data - the data to send to the view (if there's a render engine in use)
     */
    public static function render(string $uri, array $data=[]){
        $URI = self::$baseLocation . $uri;

        if(!self::hasRenderEngine()) {
            Session::set("VIEW_DATA", $data);
            Session::beginViewRendering();
            global $vd; //Use $vd has the View's datasource
            require "{$URI}";
            Session::endViewRendering();
        }else
            self::renderViaEngine($URI, $data);
    }

    /**Clears the output/rendering buffer
     */
    public static function clear(){
        ob_end_clean();
    }

    /**Clears the output/rendering buffer and renders the view
     * @param string $uri - the URI/URl to the view file to render
     * @param array $data - the data passed to the vie
     */
    public static function renderView(string $uri, array $data=[]){
        self::clear();
        self::render($uri, $data);
    }

    /**Renders a view via the registered render engine (only if the render engine is valid)
     * @param string $uri - the URI/URL to the view file to render
     * @param array $data - the data passed to the view
     */
    protected static function renderViaEngine(string $uri, array $data=[]){
        if(self::hasValidRenderEngine())
            self::$engine->render($uri, $data);
    }

    /**Defines/replaces the view render engine
     * @param $renderer - the new render engine
     * @throws Error
     */
    public static function registerRenderEngine($renderer){
        self::$engine = $renderer;

        if(!self::hasValidRenderEngine()) {
            self::removeRenderEngine();
            throw new Error("Use of an invalid view render engine.");
        }
    }

    /**Deletes the render engine in use (and sets it back to null)
     */
    public static function removeRenderEngine(){
        self::$engine = null;
    }

    public static function setBaseLocation(string $path){
        self::$baseLocation = "{$path}";
    }
}