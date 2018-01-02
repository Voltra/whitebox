<?php
namespace WhiteBox\Routing\Controllers\Annotations;

/**
 * Class Get
 * @package WhiteBox\Routing\Controllers\Annotations
 * @Annotation
 */
class Get extends A_MethodSpecializedDefineRoute{
    protected function getMethod(): string {
        return "GET";
    }
}