<?php
use WhiteBox\Rendering\Renderer;
$app->error404(function(){
    Renderer::renderView("error/404.php");
})->name("pageNotFound");