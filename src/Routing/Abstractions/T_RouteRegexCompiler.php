<?php
/////////////////////////////////////////////////////////////////////////
//Namespace
/////////////////////////////////////////////////////////////////////////
namespace WhiteBox\Routing\Abstractions;



/**A trait designating any class able to compile a Route's regex to an actual URI using a request's URI
 * Trait T_RouteRegexCompiler
 * @package WhiteBox\Routing
 */
trait T_RouteRegexCompiler{
    /////////////////////////////////////////////////////////////////////////
    //Class methods
    /////////////////////////////////////////////////////////////////////////
    /**Creates a regex from a Route's regex
     * @param string $uri_regex being the Route's regex
     * @return string
     */
    protected abstract static function makeRegex(string $uri_regex): string;

    /**Converts any non-compiled regex (wildcard) to compiled regex and return the modified string
     * @param string $uri_regex being the Route's regex
     * @return string
     */
    protected abstract static function masksToRegex(string $uri_regex): string;

    /**Retrieves the array of URI parameters from the URI dans the Route's regex
     * @param string $uri being the requested URI
     * @param string $uri_regex being the Route's regex
     * @return array
     */
    protected abstract static function uriParams(string $uri, string $uri_regex): array;
}