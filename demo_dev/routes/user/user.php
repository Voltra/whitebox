<?php

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use WhiteBox\Rendering\Renderer;

$app->get("/user/:user", function(string $user, ServerRequestInterface $request, ResponseInterface $res) use($app){
    return $app->view->render($res, "user/user.php", [
        "user" => $user,
        "phpversion" => phpversion(),
        "request" => var_export($request, true)
    ]);
})->name("user");