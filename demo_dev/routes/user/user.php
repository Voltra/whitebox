<?php

use Psr\Http\Message\ServerRequestInterface;
use WhiteBox\Rendering\Renderer;

$app->get("/user/:user", function(string $user, ServerRequestInterface $request){
    Renderer::renderView("user/user.php", [
        "user" => $user,
        "phpversion" => phpversion(),
        "request" => var_export($request, true)
    ]);
})->name("user");