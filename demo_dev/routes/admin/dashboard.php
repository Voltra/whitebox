<?php
use WhiteBox\Rendering\Renderer;

$admin->get("/dashboard", function($rq, $res) use($app){
    return $app->view->render($res, "admin/dashboard.php", [
        "phpversion" => phpversion()
    ]);
});