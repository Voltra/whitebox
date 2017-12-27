<?php
/////////////////////////////////////////////////////////////////////////
//File requirements
/////////////////////////////////////////////////////////////////////////
require_once "../vendor/autoload.php"; //Require Composer's autoloader
require_once "middlewares/AdminMiddleware.php";


/////////////////////////////////////////////////////////////////////////
//Imports
/////////////////////////////////////////////////////////////////////////
use Doctrine\Common\Annotations\AnnotationRegistry;
use WhiteBox\App;
use WhiteBox\Http\Session;
use WhiteBox\Rendering\Renderer;
use WhiteBox\Rendering\Engine\PhpHtmlRenderEngine;
use WhiteBox\Routing\SubRouter;


function relativeUrl(string $path){
    return dirname(__FILE__) . DIRECTORY_SEPARATOR . $path;
}

function bootstrap_isAdmin(){
    return function(){
        return (bool)rand(0,1);
    };
}

$app = new App(); //Creates the application/router
$admin = new SubRouter("/admin"/*, bootstrap_isAdmin()*/); //Creates a subrouter for the admin zone, gives a default authorisation middleware
$app->register($admin);//Registers the subrouter in the router
Session::start(); //Engages the use of sessions
Renderer::setBaseLocation(relativeUrl("views/")); //Sets the root location for views
Renderer::registerRenderEngine(new PhpHtmlRenderEngine()); //Even though this is the default \o/

$app->pipe(new AdminMiddleware($app));

/////////////////////////////////////////////////////////////////////////
//File requirements
/////////////////////////////////////////////////////////////////////////
/*To setup all of your Route you can use a RouteLoader (will scan recursively the folder), cf. routeLoaderGenerator*/
require_once "route_autoload.php";


$api = new ApiController();
$app->register($api);