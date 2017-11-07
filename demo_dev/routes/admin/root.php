<?php

use WhiteBox\Rendering\Renderer;

function genFalseGenerator(){
    return function(){
        return false;
    };
}

function genTrueGenerator(){
    return function(){
        return true;
    };
}

function genFalse(){
    return false;
}

$app->get("/admin", function(){
    Renderer::renderView("admin/root.php", [
        "admin" => "OMAGAD YOU AN ADMIN?",
        "phpversion" => phpversion()
    ]);
}, genTrueGenerator());