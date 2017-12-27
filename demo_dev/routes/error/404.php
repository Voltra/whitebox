<?php
use WhiteBox\Rendering\Renderer;
$app->error404(function($rq, $res){
    return Renderer::renderView($res, "error/404.php", [
        "phpversion" => phpversion()
    ]);
})->name("pageNotFound");