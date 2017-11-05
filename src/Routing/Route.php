<?php
namespace WhiteBox\Routing;

use http\Exception\InvalidArgumentException;

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

    public function __construct(string $method, string $re, callable $functor = null){
        if(in_array($method, Route::$METHODS, true))
            $this->_method = $method;
        else
            throw new InvalidArgumentException("The method must be either GET, POST, PUT, HEAD or error.");

        $this->_regex = $re;

        if(!is_null($functor))
            $this->_handler = $functor;
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
        if(is_callable($func))
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
        return isset($this->_handler);
    }
}