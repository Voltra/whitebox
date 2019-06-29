<?php
/////////////////////////////////////////////////////////////////////////
//Namespace
/////////////////////////////////////////////////////////////////////////
namespace WhiteBox\Routing\Abstractions;



/////////////////////////////////////////////////////////////////////////
//Imports
/////////////////////////////////////////////////////////////////////////
use WhiteBox\Routing\Abstractions\T_WildcardBasedRouteManager;



/**
 * Trait T_CisRouter
 * @package WhiteBox\Routing\Abstractions
 */
abstract class A_CisRouter{
    /////////////////////////////////////////////////////////////////////////
    //Traits used
    /////////////////////////////////////////////////////////////////////////
    use T_WildcardBasedArrayRouteManager{
        T_WildcardBasedArrayRouteManager::__construct as protected WBARM__construct;
    }



    /////////////////////////////////////////////////////////////////////////
    //Properties
    /////////////////////////////////////////////////////////////////////////
    /**
     * @var string
     */
    protected $prefix;

    /**
     * @var callable
     */
    protected $defaultAuthMW;



    /////////////////////////////////////////////////////////////////////////
    //Magics
    /////////////////////////////////////////////////////////////////////////
    /**
     * T_CisRouter constructor.
     * @param string $prefix
     * @param callable|null $defaultAuthMW
     */
    public function __construct(string $prefix, ?callable $defaultAuthMW = null) {
        $this->WBARM__construct();
        $this->routes = [];
        $this->prefix = $prefix;
        $this->defaultAuthMW = $defaultAuthMW ?? static function () { return true; };
    }



    /////////////////////////////////////////////////////////////////////////
    //Methods
    /////////////////////////////////////////////////////////////////////////

    /**
     * @return string
     */
    public function getPrefix(): string{
        return $this->prefix;
    }

    /**
     * @return array
     */
    public function getRoutes(): array{
        return $this->routes;
    }

    public function getDefaultAuthMiddleware(): callable{
        return $this->defaultAuthMW;
    }
}