<?php

namespace WhiteBox\Http;


interface I_Session{
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
    public static function set(string $key, $value);

    /**Removes an information from the session
     * @param string $key
     */
    public static function delete(string $key);
}