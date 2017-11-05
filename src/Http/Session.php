<?php
/**
 * Created by PhpStorm.
 * User: Ludwig
 * Date: 05/11/2017
 * Time: 13:55
 */

namespace WhiteBox\Http;

use WhiteBox\Helpers\MagicalArray;

$vd = new MagicalArray(); //View's data

class Session implements I_Session{
    /**A static method guaranteeing the fact that sessions are in use once called
     */
    public static function ensureStarted(){
        if(!self::isStarted())
            session_start();
    }

    /**A static method asserting whether or not a session is started
     * @return bool
     */
    public static function isStarted(){
        return !(session_status() === PHP_SESSION_NONE);
    }

    /**Starts a session (or resume one)
     * @param array $options
     */
    public static function start(array $options=[]){
        session_start($options);
    }

    /**Method to only used coupled with the native PHP rendering system, retrieves the view's data and sets it as a global variable
     */
    public static function beginViewRendering(){
        global $vd;
        if(self::isStarted())
            $vd = new MagicalArray( (isset($_SESSION["VIEW_DATA"]) ? $_SESSION["VIEW_DATA"] : []) );
    }

    public static function endViewRendering(){
        global $vd;
        if(self::isStarted())
            $vd = new MagicalArray();
    }

    /**Retrieves an information from the session
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function get(string $key, $default=null){
        self::ensureStarted();
        if(array_key_exists($key, $_SESSION))
            return $_SESSION[$key];
        else
            return $default;
    }

    /**Sets an information in the session
     * @param string $key
     * @param mixed $value
     */
    public static function set(string $key, $value){
        self::ensureStarted();
        $_SESSION[$key] = $value;
    }

    /**Removes an information from the session
     * @param string $key
     */
    public static function delete(string $key){
        self::ensureStarted();
        unset($_SESSION[$key]);
    }
}