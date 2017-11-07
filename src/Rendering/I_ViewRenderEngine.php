<?php
namespace WhiteBox\Rendering;

interface I_ViewRenderEngine{
    /**
     * @param string $uri
     * @param array $data
     * @return string
     */
    public function render(string $uri, array $data=[]);
}