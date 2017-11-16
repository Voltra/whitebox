<?php
/////////////////////////////////////////////////////////////////////////
//Namespace
/////////////////////////////////////////////////////////////////////////
namespace WhiteBox\Routing\Abstractions;



/////////////////////////////////////////////////////////////////////////
//Imports
/////////////////////////////////////////////////////////////////////////
use WhiteBox\Routing\Abstractions\A_WildcardBasedRouteManager;


/**
 * Class A_CisRouter
 * @package WhiteBox\Routing\Abstractions
 */
abstract class A_CisRouter extends A_WildcardBasedArrayRouteManager{
    /**
     * @var string
     */
    protected $prefix;

    /**
     * @var callable
     */
    protected $defaultAuthMW;

    /**
     * A_CisRouter constructor.
     * @param string $prefix
     * @param callable|null $defaultAuthMW
     */
    public function __construct(string $prefix, ?callable $defaultAuthMW = null) {
        parent::__construct();
        $this->routes = [];
        $this->prefix = $prefix;
        $this->defaultAuthMW = is_null($defaultAuthMW) ? function(){ return true; } : $defaultAuthMW;
    }

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