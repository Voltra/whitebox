<?php
namespace WhiteBox\Filesystem;

use Exception;
use WhiteBox\Helpers\MagicalArray;

/**An abstract class factorizing the common behaviors among the directory browsers
 * Class AbstractDirectoryBrowser
 * @package WhiteBox\Filesystem
 */
abstract class AbstractDirectoryBrowser implements I_FSBrowser{
    /////////////////////////////////////////////////////////////////////////
    //Properties
    /////////////////////////////////////////////////////////////////////////
    /**The URI to the directory to browse
     * @var string
     */
    protected $uri;



    /////////////////////////////////////////////////////////////////////////
    //Magics
    /////////////////////////////////////////////////////////////////////////
    /**Construct a browser to a directory from its uri
     * AbstractDirectoryBrowser constructor.
     * @param string $uri
     * @throws Exception
     */
    public function __construct(string $uri){
        if(!is_dir($uri))
            throw new Exception("Tried to construct a {self::class} providing the URI to a non-folder.");

        $this->uri = $uri;
    }



    /////////////////////////////////////////////////////////////////////////
    //Overrides
    /////////////////////////////////////////////////////////////////////////
    /**Converts the instance to an array
     * @return array
     */
    public function toArray(): array{
        return iterator_to_array($this->getIterator());
    }

    /**Converts the instance to a MagicalArray
     * @return MagicalArray
     */
    public function toMagicalArray(): MagicalArray{
        return new MagicalArray(
            $this->toArray()
        );
    }
}