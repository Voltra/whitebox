<?php
namespace WhiteBox\Helpers;


/**Describes the behavior of a class that could be serialized in the JSON format
 * Interface I_JsonSerializable
 * @package WhiteBox\Helpers
 */
interface I_JsonSerializable{
    /////////////////////////////////////////////////////////////////////////
    //Class methods
    /////////////////////////////////////////////////////////////////////////
    /**Creates an instance from a JSON string
     * @param string $json being the JSON string representing the object to create
     * @return I_JsonSerializable
     */
    public static function fromJson(string $json);



    /////////////////////////////////////////////////////////////////////////
    //Methods
    /////////////////////////////////////////////////////////////////////////
    /**Converts the instance back to JSON (as a string)
     * @return string
     */
    public function toJson(): string;
}