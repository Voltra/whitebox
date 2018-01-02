<?php
namespace WhiteBox\Routing\Controllers\Annotations;

/**
 * Class Post
 * @package WhiteBox\Routing\Controllers\Annotations
 * @Annotation
 */
class Post extends A_MethodSpecializedDefineRoute {
    protected function getMethod(): string {
        return "POST";
    }
}