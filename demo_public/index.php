<?php
//TODO: DISABLE DEM FUCKING WARNINGS

require_once "../vendor/autoload.php"; //Require Composer's autoloader

use WhiteBox\Rendering\Engine\PhpHtmlRenderEngine;
use WhiteBox\Routing\Router;
use WhiteBox\Http\Session;
use WhiteBox\Rendering\Renderer;

$app = new Router(); //Creates the application/router
Session::start(); //Engages the use of sessions
Renderer::setBaseLocation("../demo_dev/views/"); //Sets the root location for views
Renderer::registerRenderEngine(new PhpHtmlRenderEngine()); //Even though this is the default \o/

require_once "../demo_dev/routes.php"; //Setup all routes

$app->run(); //Runs the application