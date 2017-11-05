<?php
namespace WhiteBox\Http;

use HttpRequestMethodException;
use WhiteBox\Helpers\MagicalArray;

class Request{
    const BAD_GET = "Tried to access GET data from a non-GET request";
    const BAD_POST = "Tried to access POST data from a non-POST request";

    protected $dataSource;

    public function __construct(){
        $this->dataSource = array_merge($_SERVER);
    }

    public function queryString(){ return $this->dataSource["QUERY_STRING"]; }
    public function requestURI(){ return $this->dataSource["REQUEST_URI"]; }

    public function getMethod(){ return $this->dataSource["REQUEST_METHOD"]; }
    public function isPost(){ return $this->getMethod()==="POST"; }
    public function isGet(){ return $this->getMethod()==="GET"; }
    public function isHead(){ return $this->getMethod()==="HEAD"; }
    public function isPut(){ return $this->getMethod()==="PUT"; }

    public function post(string $key){
        if($this->isPost())
            return (isset($_POST[$key]) ? $_POST[$key] : ""); //"" or null
        else
            throw new HttpRequestMethodException(self::BAD_POST);
    }
    public function get(string $key){
        if($this->isGet())
            return (isset($_GET[$key]) ? $_GET[$key] : ""); //"" or null
        else
            throw new HttpRequestMethodException(self::BAD_GET);
    }
    public function cookie(string $key){
        return (isset($_COOKIE[$key]) ? $_COOKIE[$key] : null);
    }

    public function setCookie(string $key, $value="", $exp=0, $path="", $domain="", bool $secure=false, bool $httpOnly=false){
        setcookie($key, $value, $exp, $path, $domain, $secure, $httpOnly);
        return $this;
    }


    public function getParams(){
        if($this->isGet())
            return new MagicalArray( array_merge($_GET, []) );
        else
            throw new HttpRequestMethodException(self::BAD_GET);
    }

    public function postParams(){
        if($this->isPost())
            return new MagicalArray( array_merge($_POST, []) );
        else
            throw new HttpRequestMethodException(self::BAD_POST);
    }
}