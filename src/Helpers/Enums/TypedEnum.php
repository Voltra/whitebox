<?php
/////////////////////////////////////////////////////////////////////////
//Namespace
/////////////////////////////////////////////////////////////////////////
namespace WhiteBox\Helpers\Enums;



/////////////////////////////////////////////////////////////////////////
//Imports
/////////////////////////////////////////////////////////////////////////
use WhiteBox\Helpers\Enums\Enum;


/**An Enum that wants to be restricted to a certain type
 * Class TypedEnum
 * @package WhiteBox\Helpers\Enums
 */
abstract class TypedEnum extends Enum{
    /////////////////////////////////////////////////////////////////////////
    //Overrides
    /////////////////////////////////////////////////////////////////////////
    public static function toArray(): array{
        return array_filter(parent::toArray(), [static::class, "isOfCorrectType"]);
    }

    public abstract static function isOfCorrectType($value): bool;
}