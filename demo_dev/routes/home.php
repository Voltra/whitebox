<?php

use WhiteBox\Rendering\Renderer;

$app->get("/", function($rq, $res){
    return Renderer::renderView($res, "home.php", [
        "version" => phpversion()
    ]);
})->name("home");