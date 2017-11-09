<?php
namespace WhiteBox\Rendering;

/**An Interface used to represent a view render engine's behavior
 * Interface I_ViewRenderEngine
 * @package WhiteBox\Rendering
 */
interface I_ViewRenderEngine{
    /////////////////////////////////////////////////////////////////////////
    //Methods
    /////////////////////////////////////////////////////////////////////////
    /**Renders a HTML string from a URI to a view file
     * @param string $uri being the URI to the view file
     * @param array $data being the data to pass to the view (associative array)
     * @return string
     */
    public function render(string $uri, array $data=[]): string;
}