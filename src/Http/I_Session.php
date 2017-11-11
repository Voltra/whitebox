<?php
/////////////////////////////////////////////////////////////////////////
//Namespace
/////////////////////////////////////////////////////////////////////////
namespace WhiteBox\Http;



/**An interface designed to wrap the native PHP session system in a fancy way
 * Interface I_Session
 * @package WhiteBox\Http
 */
interface I_Session{
    /////////////////////////////////////////////////////////////////////////
    //Class methods
    /////////////////////////////////////////////////////////////////////////
    /**Retrieves an information from the session
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function get(string $key, $default);


    /**Sets an information in the session
     * @param string $key
     * @param mixed $value
     */
    public static function set(string $key, $value): void;

    /**Removes an information from the session
     * @param string $key
     */
    public static function delete(string $key): void;
}