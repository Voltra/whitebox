<?php

use WhiteBox\Rendering\Renderer;

$app->get("/", function($rq, $res) use($app){
    return $app->view->render($res, "home.php", [
        "version" => phpversion()
    ]);
})->name("home");