<?php

use WhiteBox\AppConfig;
use WhiteBox\Rendering\Engine\PhpHtmlRenderEngine;

function relUrl(string $path){
    return dirname(__FILE__) . DIRECTORY_SEPARATOR . $path;
}

return [
    "config" => new AppConfig([]),
    "view" => function(){
        $renderEngine = new PhpHtmlRenderEngine();
        $renderEngine->setBaseUri( relUrl("views/") );
        return $renderEngine;
    }
];