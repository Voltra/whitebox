<?php
/**
 * Created by PhpStorm.
 * User: Ludwig
 * Date: 05/11/2017
 * Time: 13:55
 */

namespace WhiteBox\Http;

/**An implementation of I_Session using the native PHP session system
 * Class Session
 * @package WhiteBox\Http
 */
class Session implements I_Session{
    /////////////////////////////////////////////////////////////////////////
    //Class methods
    /////////////////////////////////////////////////////////////////////////
    /**A static method guaranteeing the fact that sessions are in use once called
     */
    public static function ensureStarted(): bool{
        if(!self::isStarted())
            return (bool)session_start();
        return true;
    }

    /**A static method asserting whether or not a session is started
     * @return bool
     */
    public static function isStarted(): bool{
        return session_status() !== PHP_SESSION_NONE;
    }

    /**Starts a session (or resume one)
     * @param array $options
     * @return bool
     */
    public static function start(array $options=[]): bool{
        if(!self::isStarted())
            return (bool)session_start($options);
        else
            return false;
    }

    /**Aborts a session
     * @return bool
     */
    public static function stop(): bool{
        if(self::isStarted())
            return (bool)session_abort();
        else
            return false;
    }

    /**Retrieves the session's status
     * @return int
     */
    public static function getStatus(): int{
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
    public static function set(string $key, $value): void{
        self::ensureStarted();
        $_SESSION[$key] = $value;
    }

    /**Removes an information from the session
     * @param string $key
     */
    public static function delete(string $key): void{
        self::ensureStarted();
        unset($_SESSION[$key]);
    }
}