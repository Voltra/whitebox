<?php

use WhiteBox\Rendering\Renderer;

$app->get("/", function(){
    Renderer::renderView("home.php", [
        "version" => phpversion()
    ]);
})->name("home");