<?php
/////////////////////////////////////////////////////////////////////////
//Namespace
/////////////////////////////////////////////////////////////////////////
namespace WhiteBox\Routing\Controllers\Annotations;



/////////////////////////////////////////////////////////////////////////
//Imports
/////////////////////////////////////////////////////////////////////////
use Doctrine\Common\Annotations\Annotation;
use Doctrine\Common\Annotations\Annotation\Required;
use Doctrine\Common\Annotations\Annotation\Target;
use Doctrine\Common\Annotations\AnnotationException;



/**
 * An annotation used to define a route on a controller's method
 * Class Routing
 * @package WhiteBox\Routing\Controllers
 * @Annotation
 * @Target("METHOD")
 */
class DefineRoute{
    /////////////////////////////////////////////////////////////////////////
    //Properties
    /////////////////////////////////////////////////////////////////////////
    /**
     * @var string
     * @Required
     */
    public $method;

    /**
     * @var string
     * @Required
     */
    public $uri;

    /**
     * @var string
     */
    public $name;



    /////////////////////////////////////////////////////////////////////////
    //Magics
    /////////////////////////////////////////////////////////////////////////
    /**
     * DefineRoute constructor.
     * @param array $values
     */
    public function __construct(array $values) {
        $requiredKeys = ["method", "uri"];
        array_walk($requiredKeys, function(string $requiredKey) use($values){
            if(!array_key_exists($requiredKey, $values))
                throw new AnnotationException("Tried to construct a DefineRoute annotation without value for '{$requiredKey}'");
        });

        $this->method = $values["method"];
        $this->uri = $values["uri"];
        $this->name = $values["name"] ?? null;
    }
}