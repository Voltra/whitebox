<?php
namespace WhiteBox\Rendering\Engine;

use WhiteBox\Helpers\MagicalArray;
use WhiteBox\Http\Session;
use WhiteBox\Rendering\I_ViewRenderEngine;

$vd = new MagicalArray(); //View's data

class PhpHtmlRenderEngine implements I_ViewRenderEngine {
    /**
     * @param string $uri
     * @param array $data
     * @return string
     */
    public function render(string $uri, array $data = []){
        Session::set("VIEW_DATA", $data);
        self::beginViewRendering();
        ob_start();
        global $vd;
        require "{$uri}";
        self::endViewRendering();
        return ob_get_clean();
    }


    public static function beginViewRendering(){
        global $vd;
        if(Session::isStarted())
            $vd = new MagicalArray( (is_null(Session::get("VIEW_DATA")) ? [] : Session::get("VIEW_DATA")) );
    }

    public static function endViewRendering(){
        global $vd;
        if(Session::isStarted()) {
            $vd = new MagicalArray();
            Session::set("VIEW_DATA", []);
        }
    }
}