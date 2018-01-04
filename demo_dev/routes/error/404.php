<?php
use WhiteBox\Rendering\Renderer;
$app->error404(function($rq, $res) use($app){
    return $app->view->render($res, "error/404.php", [
        "phpversion" => phpversion()
    ]);
})->name("pageNotFound");