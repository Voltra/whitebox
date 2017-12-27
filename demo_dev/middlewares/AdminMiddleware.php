<?php

use WhiteBox\Helpers\RegexHandler;
use WhiteBox\Middlewares\A_Middleware;

class AdminMiddleware extends A_Middleware{
    public function process(\Psr\Http\Message\ServerRequestInterface $rq, ?A_Middleware $next, \WhiteBox\App $app) {
        $uri = $rq->getUri()->getPath();
        $requiresAdmin = new RegexHandler("/^\/admin/");//Uri starts with "/admin"

        if($requiresAdmin->appliesTo($uri)){
            if($this->isAdmin()){
                if(!is_null($next))
                    return $next->process($rq, null, $app);
                return true;
            }else
                return $app->redirectTo("home");
        }
    }

    protected function isAdmin(){
        return (bool)rand(0,1);
    }
}