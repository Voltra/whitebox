<?php
/////////////////////////////////////////////////////////////////////////
//Namespace
/////////////////////////////////////////////////////////////////////////
namespace WhiteBox\Middlewares;



/////////////////////////////////////////////////////////////////////////
//Imports
/////////////////////////////////////////////////////////////////////////
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;



trait T_MiddlewareHub {
    /////////////////////////////////////////////////////////////////////////
    //Properties
    /////////////////////////////////////////////////////////////////////////
    /**
     * @var A_Middleware[]
     */
    protected $middlewares;

    /**
     * @var int
     */
    protected $index;



    /////////////////////////////////////////////////////////////////////////
    //Magics
    /////////////////////////////////////////////////////////////////////////
    public function __construct(){
        $this->middlewares = [];
        $this->index = 0;
    }



    /////////////////////////////////////////////////////////////////////////
    //Methods
    /////////////////////////////////////////////////////////////////////////
    /**Determines whether or not there is already a middleware in this middleware hub
     * @param string $class being the class of the requested middleware
     * @return bool
     */
    protected function hasMiddleware(string $class) : bool{
        $classes = array_map(static function(A_Middleware $m){
            return get_class($m);
        }, $this->middlewares);

        return in_array($class, $classes);
    }

    /**Pipes a middleware to the middleware execution queue
     * @param A_Middleware $middleware being the new middleware to add
     * @return $this
     */
    public function pipe(A_Middleware $middleware){
        if(!$this->hasMiddleware(get_class($middleware)))
            $this->middlewares[] = $middleware;

        return $this;
    }

    /**Retrieves the current middleware (the one that needs to be executed)
     * @return null|A_Middleware
     */
    protected function getCurrent(): ?A_Middleware{
        if(isset($this->middlewares[$this->index]))
            return $this->middlewares[$this->index];

        return null;
    }

    /**Processes the entire middleware queue in a fancy recursive manner
     * @param ServerRequestInterface $rq being the current HTTP request
     * @param ResponseInterface $res being the current HTTP response
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $rq, ResponseInterface $res): ResponseInterface{
        $current = $this->getCurrent();
        $this->index += 1;

        if($current === null)
            return $res;
        else
            return $current->process($rq, $res, [$this, "process"]);
    }
}