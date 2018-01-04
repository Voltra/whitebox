<?php
/////////////////////////////////////////////////////////////////////////
//Namespace
/////////////////////////////////////////////////////////////////////////
namespace WhiteBox\Routing\Controllers\Annotations;



/**An abstract class that represents the shared behavior of annotations that allow to define a route for a specific HTTP request method
 * Class A_MethodSpecializedDefineRoute
 * @package WhiteBox\Routing\Controllers\Annotations
 */
abstract class A_MethodSpecializedDefineRoute extends DefineRoute {
    /////////////////////////////////////////////////////////////////////////
    //Methods
    /////////////////////////////////////////////////////////////////////////
    protected abstract function getMethod(): string;



    /////////////////////////////////////////////////////////////////////////
    //Overrides
    /////////////////////////////////////////////////////////////////////////
    public function __construct(array $values){
        $definedValues = array_merge($values, [
            "method" => $this->getMethod()
        ]);
        parent::__construct($definedValues);
    }
}