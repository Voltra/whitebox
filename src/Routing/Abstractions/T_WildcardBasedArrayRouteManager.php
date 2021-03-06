<?php
/////////////////////////////////////////////////////////////////////////
//Namespace
/////////////////////////////////////////////////////////////////////////
namespace WhiteBox\Routing\Abstractions;



/////////////////////////////////////////////////////////////////////////
//Imports
/////////////////////////////////////////////////////////////////////////
use WhiteBox\Helpers\RegexHandler;
use WhiteBox\Routing\Route;



/**
 * Trait T_WildcardBasedArrayRouteManager
 * @package WhiteBox\Routing\Abstractions
 */
trait T_WildcardBasedArrayRouteManager{
    /////////////////////////////////////////////////////////////////////////
    //Traits used
    /////////////////////////////////////////////////////////////////////////
    use T_WildcardBasedRouteManager;



    /////////////////////////////////////////////////////////////////////////
    //Properties
    /////////////////////////////////////////////////////////////////////////
    /**The routes registered in this Router
     * @var Route[]
     */
    protected $routes;



    /////////////////////////////////////////////////////////////////////////
    //Class properties
    /////////////////////////////////////////////////////////////////////////
    /**The default wildcards used to ease the regex experience for routes
     * @var array
     */
    protected static $wildcards = [
        "/:ALNUM/" => "([A-Z0-9_]+)",
        "/:alnum/" => "([a-z0-9_]+)",
        "/:word/" => "(\w+)",
        "/:digit[^s]*/" => "(\d)", //only matches "digit" and not "digits" => digit not followed by an s
        "/:digits/" => "(\d+)",
        "/:alpha/" => "([a-z]+)",
        "/:ALPHA/" => "([A-Z]+)"
    ];

    /**The core wildcards (keys)
     * @var array
     */
    private static $coreWildcards = null;

    /**Determines whether or not the Router's core wildcards are registered or not
     * @var bool
     */
    private static $isInitialized = false;


    /////////////////////////////////////////////////////////////////////////
    //Magics
    /////////////////////////////////////////////////////////////////////////
    /**Construct a Router
     * Router constructor.
     */
    public function __construct(){
        $this->routes = [
            new Route("error", "404", function(){ echo "<b>Error 404</b>"; })
        ];
    }


    /**Retrieves the regex/URL associated to the Route that has the given routeName
     * @param string $routeName being the name of the Route to lookup the url for
     * @param array|null $uriParams being the URI params to pass to build the url (format being : "/user/:id" would give ["id"=>1]
     * @return string
     */
    public function urlFor(string $routeName, ?array $uriParams = null): string {
        $name = (string)$routeName;

        foreach ($this->routes as $route) {
            if ($route->getName() === $name) {
                if($uriParams === null)
                    return $route->regex();
                else {
                    $uriKeys = array_keys($uriParams);
                    $uriValues = array_values($uriParams);
                    $uriParameters = array_map(static function($key, $value){
                        return [
                            "key" => $key,
                            "value" => $value
                        ];
                    }, $uriKeys, $uriValues);

                    return array_reduce($uriParameters, static function (string $routeBuilt, array $uriParam): string {
                        $key = (string)$uriParam["key"];
                        $value = (string)$uriParam["value"];
                        return preg_replace("/:{$key}/", $value, $routeBuilt, 1);
                    }, $route->regex());
                }
            }
        }

        return "";
    }


    /////////////////////////////////////////////////////////////////////////
    //Overrides
    /////////////////////////////////////////////////////////////////////////
    /**A static method initializing the core wildcards if they are not initialized
     */
    public static function initCoreWildcards(): void{
        if(!self::$isInitialized) {
            self::$coreWildcards = array_keys(self::$wildcards);
            self::$isInitialized = true;
        }
    }

    /**Registers a wildcard (only if it doesn't exist)
     * @param string $wildcard being the wildcard identifier/non-compiled regex (eg. "/:wildcard/")
     * @param string $regex being the compiled regex for the wildcard
     */
    public static function registerWildcard(string $wildcard, string $regex): void{
        if(!array_key_exists($wildcard, self::$wildcards))
            self::$wildcards[$wildcard] = $regex;
    }

    /**Removes a (non core) wildcard
     * @param string $wildcard being the wildcard identifier/non-compiled regex of the wildcard to remove
     */
    public static function removeWildcard(string $wildcard): void{
        if(
            !array_key_exists($wildcard, self::$coreWildcards)
            && array_key_exists($wildcard, self::$wildcards)
        )
            unset(self::$wildcards[$wildcard]);
    }

    /**Registers a wildcard as an alias of an already registered wildcard
     * @param string $alias being the wildcard identifier/non-compiled regex of the new wildcard
     * @param string $current being the the wildcard identifier/non-compiled regex of the aliased wildcard
     */
    public static function registerAliasWildcard(string $alias, string $current): void{
        if(
            !array_key_exists($alias, self::$wildcards)
            && array_key_exists($current, self::$wildcards)
        )
            self::registerWildcard($alias, self::$wildcards[$current]);
    }

    /**Creates a regex from a Route's regex
     * @param string $uri_regex being the Route's regex
     * @return string
     */
    protected static function makeRegex(string $uri_regex): string{
        return "/^" . str_replace("/", "\/", self::masksToRegex($uri_regex)) . "$/";
    }

    /**Converts any non-compiled regex (wildcard) to compiled regex and return the modified string
     * @param string $uri_regex being the Route's regex
     * @return string
     */
    protected static function masksToRegex(string $uri_regex): string{
        $re = (string)$uri_regex;

        foreach(self::$wildcards as $pattern=>$replacement) //Replaces all defined wildcards before default wildcard
            $re = preg_replace($pattern, $replacement, $re);

        return (string)preg_replace("/:(\w+)/i", "([^/]+)", $re);
    }

    /**Retrieves the array of URI parameters from the URI and the Route's regex
     * @param string $uri being the requested URI
     * @param string $uri_regex being the Route's regex
     * @return array
     */
    protected static function uriParams(string $uri, string $uri_regex): array{
        $uri_regex = self::masksToRegex($uri_regex);
        $regex = new RegexHandler(self::makeRegex($uri_regex));

        return array_map("urldecode", $regex->getGroups($uri));
    }
}