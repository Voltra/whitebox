<?php
namespace WhiteBox\Http;

use HttpRequestMethodException;
use WhiteBox\Helpers\MagicalArray;

/**A class used to wrap the native PHP HTTP request objects
 * Class AdvancedRequest
 * @package WhiteBox\Http
 */
class AdvancedRequest{
    /////////////////////////////////////////////////////////////////////////
    //Class constants
    /////////////////////////////////////////////////////////////////////////
    /**Constant string used as the bad get operation usage error message
     * @var string
     */
    const BAD_GET = "Tried to access GET data from a non-GET request";

    /**Constant string used as the bad post operation usage error message
     * @var string
     */
    const BAD_POST = "Tried to access POST data from a non-POST request";



    /////////////////////////////////////////////////////////////////////////
    //Properties
    /////////////////////////////////////////////////////////////////////////
    /**The data source of this AdvancedRequest (associative array)
     * @var array
     */
    protected $dataSource;



    /////////////////////////////////////////////////////////////////////////
    //Magics
    /////////////////////////////////////////////////////////////////////////
    /**Constructs a AdvancedRequest from the $_SERVER variable
     * AdvancedRequest constructor.
     */
    public function __construct(){
       $this->dataSource = array_merge($_SERVER);
    }



    /////////////////////////////////////////////////////////////////////////
    //Methods
    /////////////////////////////////////////////////////////////////////////
    /**Retrieves the query string for the request
     * @return string
     */
    public function queryString(): string{ return $this->dataSource["QUERY_STRING"]; }

    /**Retrieves the request's URI
     * @return string
     */
    public function requestURI(): string{ return $this->dataSource["REQUEST_URI"]; }



    /**Retrieves the request's method
     * @return string
     */
    public function getMethod(): string{ return $this->dataSource["REQUEST_METHOD"]; }

    /**Determines whether or not this is a POST request
     * @return bool
     */
    public function isPost(): bool{ return $this->getMethod()==="POST"; }

    /**Determines whether or not this a GET request
     * @return bool
     */
    public function isGet(): bool{ return $this->getMethod()==="GET"; }

    /**Determines whether or not this is a HEAD request
     * @return bool
     */
    public function isHead(): bool{ return $this->getMethod()==="HEAD"; }

    /**Determines whether or not this is a DELETE request
     * @return bool
     */
    public function isPut(): bool{ return $this->getMethod()==="PUT"; }


    /**Retrieves a value from the POST request from its key
     * (defaults to "")
     * @param string $key being the key to the desired data
     * @return string
     * @throws HttpRequestMethodException
     */
    public function post(string $key): string{
        if($this->isPost())
            return (isset($_POST[$key]) ? $_POST[$key] : ""); //"" or null
        else
            throw new HttpRequestMethodException(self::BAD_POST);
    }

    /**Retrieves a value from the GET request from its key
     * (defaults to "")
     * @param string $key
     * @return string
     * @throws HttpRequestMethodException
     */
    public function get(string $key): string{
        if($this->isGet())
            return (isset($_GET[$key]) ? $_GET[$key] : ""); //"" or null
        else
            throw new HttpRequestMethodException(self::BAD_GET);
    }

    /**Retrieve a cookie from its key (name/id/identifier)
     * @param string $key being the identifier of the desired cookie
     * @return string|null
     */
    public function cookie(string $key): ?string{
        return (isset($_COOKIE[$key]) ? $_COOKIE[$key] : null);
    }

    /**Sets/creates a cookie using the given key (id, etc...), value and extra parameters
     * @param string $key being the identifier of the cookie
     * @param string $value (optional) being the value of the cookie
     * @param int $exp (optional) being the expiration date of the cookie
     * @param string $path (optional) being the path to which send the cookie
     * @param string $domain (optional) being the domain to which send the cookie
     * @param bool $secure (optional) determining whether or not to use secure cookies
     * @param bool $httpOnly (optional) determining whether or not to use HTTP only
     * @return $this
     */
    public function setCookie(string $key, string $value="", $exp=0, string $path="", string $domain="", bool $secure=false, bool $httpOnly=false): self{
        setcookie($key, $value, $exp, $path, $domain, $secure, $httpOnly);
        return $this;
    }

    /**Retrieve all the "parameters" of the GET request
     * @return MagicalArray
     * @throws HttpRequestMethodException
     */
    public function getParams(): MagicalArray{
        if($this->isGet())
            return new MagicalArray( array_merge($_GET, []) );
        else
            throw new HttpRequestMethodException(self::BAD_GET);
    }

    /**Retrieve all the "parameters" of the POST request
     * @return MagicalArray
     * @throws HttpRequestMethodException
     */
    public function postParams(): MagicalArray{
        if($this->isPost())
            return new MagicalArray( array_merge($_POST, []) );
        else
            throw new HttpRequestMethodException(self::BAD_POST);
    }

    /////////////////////////////////////////////////////////////////////////
    //Class methods
    /////////////////////////////////////////////////////////////////////////
    /**Construct a AdvancedRequest from the global $_SERVER variable
     * @return AdvancedRequest
     */
    public static function fromGlobals(): self{
        $rq = new AdvancedRequest();
        $rq->dataSource = array_merge($_SERVER);
        return $rq;
    }
}