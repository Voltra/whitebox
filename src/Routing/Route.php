<?php
namespace WhiteBox\Routing;

use InvalidArgumentException;

class Route{
    public static $METHODS = [
        "GET",
        "POST",
        "PUT",
//        "DELETE",
        "HEAD",
        "error"
    ];

    protected $_name;
    protected $_regex;
    protected $_method;
    protected $_handler;
    protected $_authorisationMiddleware;

    public function __construct(string $method, string $re, callable $functor = null, callable $authMiddleware = null){
        if(in_array($method, Route::$METHODS, true))
            $this->_method = $method;
        else
            throw new InvalidArgumentException("The method must be either GET, POST, PUT, HEAD or error.");

        $this->_regex = $re;

        if(!is_null($functor))
            $this->_handler = $functor;

        if(!is_null($functor))
            $this->_authorisationMiddleware = $authMiddleware;
        else
            $this->_authorisationMiddleware = function(){
                return true;
            };

        $this->_name = null;
    }

    public function name(string $name){
        $this->_name = "{$name}";
        return $this;
    }

    public function getName(){ return $this->_name; }

    public function method(){
        return $this->_method;
    }

    public function regex(){
        return $this->_regex;
    }

    public function equals(Route $route, bool $strict = false){
        $truth = $strict ? $this->_name === $route->_name /*&& $this->_handler==$route->getHandler()*/ : true;

        return $this->regex() === $route->regex()
            && $this->method() === $route->method()
            && $truth;
    }

    public function setHandler(callable $func){
        //if(is_callable($func))
            $this->_handler = $func;

        return $this;
    }

    public function getHandler(){
        if($this->hasHandler())
            return $this->_handler;
        else
            return null;
    }

    public function hasHandler(){
        return isset($this->_handler) && !is_null($this->_handler);
    }

    public function setAuthMiddleware(callable $functor){
        $this->_authorisationMiddleware = $functor;
        return $this;
    }

    public function getAuthMiddleware(){
        if($this->hasAuthMiddleware())
            return $this->_authorisationMiddleware;
        else
            return function(){ return true; };
    }

    public function hasAuthMiddleware(){
        return isset($this->_authorisationMiddleware) && !is_null($this->_authorisationMiddleware);
    }
}