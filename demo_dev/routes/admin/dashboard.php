<?php
use WhiteBox\Rendering\Renderer;

$admin->get("/dashboard", function($rq, $res){
    return Renderer::renderView($res, "admin/dashboard.php", [
        "phpversion" => phpversion()
    ]);
});