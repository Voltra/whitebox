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
 * Class A_RouteManager
 * @package WhiteBox\Routing\Abstractions
 */
abstract class A_RouteManager{
    /////////////////////////////////////////////////////////////////////////
    //Traits used
    /////////////////////////////////////////////////////////////////////////
    use T_RouteBuilder;
    use T_RouteStore;
}