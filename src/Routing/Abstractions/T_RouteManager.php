<?php
/////////////////////////////////////////////////////////////////////////
//Namespace
/////////////////////////////////////////////////////////////////////////
namespace WhiteBox\Routing\Abstractions;



/////////////////////////////////////////////////////////////////////////
//Imports
/////////////////////////////////////////////////////////////////////////
use WhiteBox\Routing\Abstractions\T_RouteBuilder;
use WhiteBox\Routing\Abstractions\T_RouteStore;



/**Represents an object that can both create and store Route instances
 * Trait A_RouteManager
 * @package WhiteBox\Routing\Abstractions
 */
trait T_RouteManager{
    /////////////////////////////////////////////////////////////////////////
    //Traits used
    /////////////////////////////////////////////////////////////////////////
    use T_RouteBuilder;
    use T_RouteStore;
}