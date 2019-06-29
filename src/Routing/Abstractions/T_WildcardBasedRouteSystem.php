<?php
/////////////////////////////////////////////////////////////////////////
//Namespace
/////////////////////////////////////////////////////////////////////////
namespace WhiteBox\Routing\Abstractions;



/////////////////////////////////////////////////////////////////////////
//Imports
/////////////////////////////////////////////////////////////////////////
/**Represents a class that allows to use wildcards in a Route's regex
 * Trait T_WildcardBasedRouteSystem
 * @package WhiteBox\Routing\Abstractions
 */
trait T_WildcardBasedRouteSystem{
    /////////////////////////////////////////////////////////////////////////
    //Class methods
    /////////////////////////////////////////////////////////////////////////
    /**A static method initializing the core wildcards if they are not initialized
     */
    public abstract static function initCoreWildcards(): void;


    /**Registers a wildcard (only if it doesn't exist)
     * @param string $wildcard being the wildcard identifier/non-compiled regex (eg. "/:wildcard/")
     * @param string $regex being the compiled regex for the wildcard
     */
	public abstract static function registerWildcard(string $wildcard, string $regex): void;


    /**Removes a (non core) wildcard
     * @param string $wildcard being the wildcard identifier/non-compiled regex of the wildcard to remove
     */
	public abstract static function removeWildcard(string $wildcard): void;


    /**Registers a wildcard as an alias of an already registered wildcard
     * @param string $alias being the wildcard identifier/non-compiled regex of the new wildcard
     * @param string $current being the the wildcard identifier/non-compiled regex of the aliased wildcard
     */
	public abstract static function registerAliasWildcard(string $alias, string $current);
}