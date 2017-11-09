<?php
require_once "../vendor/autoload.php"; //Require Composer's autoloader

use WhiteBox\Routing\Router;
use WhiteBox\Http\Session;
use WhiteBox\Rendering\Renderer;
use WhiteBox\Rendering\Engine\PhpHtmlRenderEngine;

function relativeUrl(string $path){
    return dirname(__FILE__) . DIRECTORY_SEPARATOR . $path;
}

$app = new Router(); //Creates the application/router
Session::start(); //Engages the use of sessions
Renderer::setBaseLocation(relativeUrl("views/")); //Sets the root location for views
Renderer::registerRenderEngine(new PhpHtmlRenderEngine()); //Even though this is the default \o/

//To setup all of your Route you can use a RouteLoader (will scan recursively the folder), cf. routeLoaderGenerator
require_once "route_autoload.php";