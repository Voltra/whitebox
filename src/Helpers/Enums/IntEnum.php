<?php
/////////////////////////////////////////////////////////////////////////
//Namespace
/////////////////////////////////////////////////////////////////////////
namespace WhiteBox\Helpers\Enums;


/////////////////////////////////////////////////////////////////////////
//Imports
/////////////////////////////////////////////////////////////////////////
use WhiteBox\Helpers\Enums\TypedEnum;



/**A TypedEnum of integers
 * Class IntEnum
 * @package WhiteBox\Helpers\Enums
 */
abstract class IntEnum extends TypedEnum{
    /////////////////////////////////////////////////////////////////////////
    //Overrides
    /////////////////////////////////////////////////////////////////////////
    public final static function isOfCorrectType($value): bool{
        return (bool)is_integer($value);
    }
}