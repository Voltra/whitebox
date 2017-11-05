<?php
namespace WhiteBox\Rendering;

interface I_ViewRenderEngine{
    public function render(string $uri, array $data=[]);
}