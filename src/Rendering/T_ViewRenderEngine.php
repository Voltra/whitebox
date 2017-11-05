<?php
namespace WhiteBox\Rendering;

trait T_ViewRenderEngine{
    public abstract function render(string $uri, array $data=[]);
}