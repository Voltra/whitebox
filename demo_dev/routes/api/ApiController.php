<?php

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use WhiteBox\Rendering\Renderer;
use WhiteBox\Routing\Controllers\A_ControllerSubRouter;
use WhiteBox\Routing\Controllers\Routing;
use WhiteBox\Routing\Controllers\SubRouting;

/**
 * Class ApiController
 * @SubRouting(prefix="/api")
 */
class ApiController extends A_ControllerSubRouter{
    /**
     * @param string $id
     * @param ServerRequestInterface $rq
     * @param ResponseInterface $res
     * @return string
     * @throws Error
     * 
     * @Routing(method="GET", uri="/grab/:id", name="api.get")
     */
    public function grab(string $id, ServerRequestInterface $rq, ResponseInterface $res){
        return Renderer::renderView($res, "api/grab.php", [
            "request" => $rq,
            "phpversion" => phpversion(),
            "id" => $id
        ]);
    }

    /**
     * @param ServerRequestInterface $rq
     * @param ResponseInterface $res
     * @return mixed
     * @throws Error
     *
     * @Routing(method="GET", uri="/test", name="api.test")
     */
    public function test(ServerRequestInterface $rq, ResponseInterface $res){
        return Renderer::render($res, "api/test.php", [
            "request" => $rq,
            "phpversion" => phpversion()
        ]);
    }
}