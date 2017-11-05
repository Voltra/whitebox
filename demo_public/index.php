<?php
require_once "../vendor/autoload.php"; //Require Composer's autoloader

use WhiteBox\Routing\Router;
use WhiteBox\Http\Session;
use WhiteBox\Rendering\Renderer;

$app = new Router(); //Creates the application/router
Session::start(); //Engages the use of sessions
Renderer::setBaseLocation("../demo_dev/views/"); //Sets the root location for views

require_once "../demo_dev/routes.php"; //Setup all routes

$app->run(); //Runs the application