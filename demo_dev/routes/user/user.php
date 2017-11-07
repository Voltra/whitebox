<?php

use WhiteBox\Rendering\Renderer;

$app->get("/user/:user", function(string $user){
    Renderer::renderView("user/user.php", [
        "user" => $user,
        "phpversion" => phpversion()
    ]);
})->name("user");