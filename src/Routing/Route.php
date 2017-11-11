<?php
/////////////////////////////////////////////////////////////////////////
//Namespace
/////////////////////////////////////////////////////////////////////////
namespace WhiteBox\Routing;



/////////////////////////////////////////////////////////////////////////
//Imports
/////////////////////////////////////////////////////////////////////////
use Closure;
use InvalidArgumentException;



/**The representation of a route in a routing system
 * Class Route
 * @package WhiteBox\Routing
 */
class Route{
    /////////////////////////////////////////////////////////////////////////
    //Class constants
    /////////////////////////////////////////////////////////////////////////
    /**A class constant array
     * @var array
     */
    const METHODS = [
        "GET",
        "POST",
        "PUT",
        "HEAD",
        "error"
    ];



    /////////////////////////////////////////////////////////////////////////
    //Properties
    /////////////////////////////////////////////////////////////////////////
    /**The name given to this Route
     * @var string|null
     */
    protected $_name;

    /**The regex used to determine whether a URI matches this Route
     * @var string
     */
    protected $_regex;

    /**The method to which this route is applied (GET, POST, etc...)
     * @var string
     */
    protected $_method;

    /**The handler attached to this Route (executed when used)
     * @var callable|Closure
     */
    protected $_handler;

    /**The middleware function used to determine whether or not the user can access this Route
     * @var callable|Closure
     */
    protected $_authorisationMiddleware;



    /////////////////////////////////////////////////////////////////////////
    //Magics
    /////////////////////////////////////////////////////////////////////////
    /**Construct a Route
     * Route constructor.
     * @param string $method being the method of this Route (GET, POST, etc...)
     * @param string $re being the regular expression of this Route
     * @param callable|null $functor being the handler of this Route
     * @param callable|null $authMiddleware being the middleware of this Route
     */
    public function __construct(string $method, string $re, callable $functor = null, callable $authMiddleware = null){
        if(in_array($method, Route::METHODS, true))
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



    /////////////////////////////////////////////////////////////////////////
    //Methods
    /////////////////////////////////////////////////////////////////////////
    /**Sets the name of this Route
     * @param string $name being the new name for this Route
     * @return $this
     */
    public function name(string $name): self{
        $this->_name = "{$name}";
        return $this;
    }

    /**Retrieves the name of this Route
     * @return string|null
     */
    public function getName(): ?string{ return $this->_name; }

    /**Retrieves the method of this Route
     * @return string
     */
    public function method(): string{
        return $this->_method;
    }

    /**Retrieve the regular expression of this Route
     * @return string
     */
    public function regex(): string{
        return $this->_regex;
    }

    /**Determines whether or not the given Route is equivalent to this Route
     * @param Route $route being the Route to compare this one to
     * @param bool $strict determining whether to use strict comparison or not
     * @return bool
     */
    public function equals(Route $route, bool $strict = false): bool{
        $truth = $strict ? $this->_name === $route->_name /*&& $this->_handler==$route->getHandler()*/ : true;

        return $this->regex() === $route->regex()
            && $this->method() === $route->method()
            && $truth;
    }

    /**Sets the handler for this Routes
     * @param callable $func being the new handler for this Route
     * @return $this
     */
    public function setHandler(callable $func): self{
        $this->_handler = $func;

        return $this;
    }

    /**Retrieves the handler attached to this Route
     * @return callable|null
     */
    public function getHandler(): ?callable{
        if($this->hasHandler())
            return $this->_handler;
        else
            return null;
    }

    /**Determines whether or not this Route has a handler attached to it
     * @return bool
     */
    public function hasHandler(): bool{
        return isset($this->_handler) && !is_null($this->_handler);
    }

    /**Sets the middleware for this Route
     * @param callable $functor being the new middleware for this route
     * @return $this
     */
    public function setAuthMiddleware(callable $functor): self{
        $this->_authorisationMiddleware = $functor;
        return $this;
    }

    /**Retrieves the middleware of this Route
     * @return callable
     */
    public function getAuthMiddleware(): callable{
        if($this->hasAuthMiddleware())
            return $this->_authorisationMiddleware;
        else
            return function(){ return true; };
    }

    /**Determines whether or not this Route has a middleware attached to it
     * @return bool
     */
    public function hasAuthMiddleware(): bool{
        return isset($this->_authorisationMiddleware) && !is_null($this->_authorisationMiddleware);
    }
}