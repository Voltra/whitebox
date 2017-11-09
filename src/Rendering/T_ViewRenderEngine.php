<?php
namespace WhiteBox\Rendering;

/**A Trait used to represent a view render engine's behavior
 * Trait T_ViewRenderEngine
 * @package WhiteBox\Rendering
 */
trait T_ViewRenderEngine{
    /////////////////////////////////////////////////////////////////////////
    //Methods
    /////////////////////////////////////////////////////////////////////////
    /**Renders a HTML string from a URI to a view file
     * @param string $uri being the URI to the view file
     * @param array $data being the data to pass to the view (associative array)
     * @return string
     */
    public abstract function render(string $uri, array $data=[]): string;
}