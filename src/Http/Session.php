<?php
/**
 * Created by PhpStorm.
 * User: Ludwig
 * Date: 05/11/2017
 * Time: 13:55
 */

namespace WhiteBox\Http;

class Session implements I_Session{
    /**A static method guaranteeing the fact that sessions are in use once called
     */
    public static function ensureStarted(){
        if(!self::isStarted())
            return session_start();
        return true;
    }

    /**A static method asserting whether or not a session is started
     * @return bool
     */
    public static function isStarted(){
        return session_status() !== PHP_SESSION_NONE;
    }

    /**Starts a session (or resume one)
     * @param array $options
     * @return bool
     */
    public static function start(array $options=[]){
        if(!self::isStarted())
            return session_start($options);
        else
            return false;
    }

    /**Aborts a session
     * @return bool
     */
    public static function stop(){
        if(self::isStarted())
            return session_abort();
        else
            return false;
    }

    /**Retrieves the session's status
     * @return int
     */
    public static function getStatus(){
        return session_status();
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