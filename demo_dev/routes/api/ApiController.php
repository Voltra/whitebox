<?php

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use WhiteBox\Rendering\Renderer;
use WhiteBox\Routing\Controllers\A_ControllerSubRouter;
use WhiteBox\Routing\Controllers\Annotations\DefineRoute;
use WhiteBox\Routing\Controllers\Annotations\DefineSubRouter;
use WhiteBox\Routing\Controllers\Annotations\Get;

/**
 * Class ApiController
 * @DefineSubRouter(prefix="/api")
 */
class ApiController extends A_ControllerSubRouter{
    /**
     * @param string $id
     * @param ServerRequestInterface $rq
     * @param ResponseInterface $res
     * @return string
     * @throws Error
     *
     * @Get(uri="/grab/:id", name="api.get")
     */
    public function grab(string $id, ServerRequestInterface $rq, ResponseInterface $res){
        return $this->view->render($res, "api/grab.php", [
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
     * @DefineRoute(method="GET", uri="/test", name="api.test")
     */
    public function test(ServerRequestInterface $rq, ResponseInterface $res){
        return $this->view->render($res, "api/test.php", [
            "request" => $rq,
            "phpversion" => phpversion()
        ]);
    }
}