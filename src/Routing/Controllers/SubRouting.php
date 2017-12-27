<?php
namespace WhiteBox\Routing\Controllers;
use Doctrine\Common\Annotations\Annotation;
use Doctrine\Common\Annotations\Annotation\Required;
use Doctrine\Common\Annotations\Annotation\Target;
use Doctrine\Common\Annotations\AnnotationException;
use Doctrine\Common\Annotations\AnnotationRegistry;

/**
 * Class SubRouting
 * @package WhiteBox\Routing\Controllers
 * @Annotation
 * @Target("CLASS")
 */
class SubRouting extends Annotation{
    /**
     * @var string
     * @Required
     */
    public $prefix;
}