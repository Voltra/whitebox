<?php
/////////////////////////////////////////////////////////////////////////
//Namespace
/////////////////////////////////////////////////////////////////////////
namespace WhiteBox\Helpers\Enums;



/////////////////////////////////////////////////////////////////////////
//Imports
/////////////////////////////////////////////////////////////////////////
use WhiteBox\Helpers\Enums\TypedEnum;



/**A TypedEnum of floating point numbers
 * Class FloatEnum
 * @package WhiteBox\Helpers\Enums
 */
abstract class FloatEnum extends TypedEnum{
    public final static function isOfCorrectType($value): bool{
        return (bool)is_float($value);
    }
}