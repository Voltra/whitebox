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

$admin->get("/", function($rq, $res) use($app){
    return $app->view->render($res, "admin/root.php", [
        "admin" => "OMAGAD YOU AN ADMIN?",
        "phpversion" => phpversion()
    ]);
});