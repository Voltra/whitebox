<?php
namespace WhiteBox\Routing\Controllers;
use Doctrine\Common\Annotations\Annotation;
use Doctrine\Common\Annotations\Annotation\Required;
use Doctrine\Common\Annotations\Annotation\Target;
use Doctrine\Common\Annotations\AnnotationException;
use Doctrine\Common\Annotations\AnnotationRegistry;
use PHPUnit\Runner\Exception;

/**
 * Class Routing
 * @package WhiteBox\Routing\Controllers
 * @Annotation
 * @Target("METHOD")
 */
class Routing{
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

    public function __construct(array $values) {
        $this->method = $values["method"];
        $this->uri = $values["uri"];
        $this->name = isset($values["name"]) ? $values["name"] : null;
    }
}