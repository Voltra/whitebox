<?php
namespace WhiteBox\Rendering;

trait T_ViewRenderEngine{
    /**
     * @param string $uri
     * @param array $data
     * @return string
     */
    public abstract function render(string $uri, array $data=[]);
}