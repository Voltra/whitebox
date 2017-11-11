<?php
/////////////////////////////////////////////////////////////////////////
//Namespace
/////////////////////////////////////////////////////////////////////////
namespace WhiteBox\Helpers;



/////////////////////////////////////////////////////////////////////////
//Class methods
/////////////////////////////////////////////////////////////////////////
abstract class NamespaceResolver{
    /**Retrieves the namespace of the given class (use Class::class)
     * @param string $classID being the class to retrieve the namespace of
     * @return null|string
     */
    public static function getFrom(string $classID): ?string{
        if(class_exists($classID))
            return substr($classID, 0, strrpos($classID, '\\'));
        else
            return null;
    }
}