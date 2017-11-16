<?php

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

function genRandomBoolGenerator(){
    return function(){
        return (bool)rand(0,1);
    };
}

use WhiteBox\Rendering\Renderer;

function isAdmin(){
    return genRandomBoolGenerator();
}

$admin->get("/", function(){
    Renderer::renderView("admin/root.php", [
        "admin" => "OMAGAD YOU AN ADMIN?",
        "phpversion" => phpversion()
    ]);
}/*, isAdmin() genFalseGenerator()*/);

$admin->get("/dashboard", function(){
    Renderer::renderView("admin/dashboard.php", [
        "phpversion" => phpversion()
    ]);
});