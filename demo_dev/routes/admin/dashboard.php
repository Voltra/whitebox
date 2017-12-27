<?php
use WhiteBox\Rendering\Renderer;

$admin->get("/dashboard", function(){
    Renderer::renderView("admin/dashboard.php", [
        "phpversion" => phpversion()
    ]);
});