<?php

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use WhiteBox\Rendering\Renderer;
use WhiteBox\Routing\A_ControllerSubRouter;

class ApiController extends A_ControllerSubRouter{
    /**
     * @param ServerRequestInterface $rq
     * @param ResponseInterface $res
     * @return mixed
     * @throws Error
     */
    public function GET_test(ServerRequestInterface $rq, ResponseInterface $res){
        return Renderer::render($res, "api/test.php", [
            "request" => $rq,
            "phpversion" => phpversion()
        ]);
    }
}