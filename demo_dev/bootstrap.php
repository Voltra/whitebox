<?php
/////////////////////////////////////////////////////////////////////////
//File requirements
/////////////////////////////////////////////////////////////////////////
require_once "../vendor/autoload.php"; //Require Composer's autoloader
require_once "middlewares/AdminMiddleware.php";
require_once "middlewares/ChangesNothingMiddleware.php";

/////////////////////////////////////////////////////////////////////////
//Imports
/////////////////////////////////////////////////////////////////////////
use WhiteBox\App;
use WhiteBox\Http\Session;
use WhiteBox\Routing\SubRouter;


function relativeUrl(string $path){
    return dirname(__FILE__) . DIRECTORY_SEPARATOR . $path;
}

Session::start(); //Engages the use of sessions
$app = new App(); //Creates the application/router
$config = require("config.php");
$app->loadConfig($config);//load the dependency injection config

$admin = new SubRouter("/admin"); //Creates a subrouter for the admin zone
$app->register($admin);//Registers the subrouter in the router

$app->pipe(new ChangesNothingMiddleware())
->pipe(new AdminMiddleware($app));

/////////////////////////////////////////////////////////////////////////
//File requirements
/////////////////////////////////////////////////////////////////////////
/*To setup all of your Route you can use a RouteLoader (will scan recursively the folder), cf. routeLoaderGenerator*/
require_once "route_autoload.php";

$api = new ApiController($app->view);
$app->register($api);