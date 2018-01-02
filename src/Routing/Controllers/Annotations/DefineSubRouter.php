<?php
namespace WhiteBox\Routing\Controllers\Annotations;

use Doctrine\Common\Annotations\Annotation;
use Doctrine\Common\Annotations\Annotation\Required;
use Doctrine\Common\Annotations\Annotation\Target;

/**
 * Class SubRouting
 * @package WhiteBox\Routing\Controllers
 * @Annotation
 * @Target("CLASS")
 */
class DefineSubRouter extends Annotation{
    /**
     * @var string
     * @Required
     */
    public $prefix;
}