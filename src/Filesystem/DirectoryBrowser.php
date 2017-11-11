<?php
/////////////////////////////////////////////////////////////////////////
//Namespace
/////////////////////////////////////////////////////////////////////////
namespace WhiteBox\Filesystem;



/////////////////////////////////////////////////////////////////////////
//Imports
/////////////////////////////////////////////////////////////////////////
use DirectoryIterator;
use IteratorIterator;
use Traversable;
use WhiteBox\Filesystem\AbstractDirectoryBrowser;



/**A class used to browse (non recursively) a directory
 * Class DirectoryBrowser
 * @package WhiteBox\Filesystem
 */
class DirectoryBrowser extends AbstractDirectoryBrowser{
    /////////////////////////////////////////////////////////////////////////
    //Magics
    /////////////////////////////////////////////////////////////////////////
    /**Construct a browser to a directory from its URI
     * DirectoryBrowser constructor.
     * @param string $uri
     */
    public function __construct(string $uri){
        parent::__construct($uri);
    }



    /////////////////////////////////////////////////////////////////////////
    //Overrides
    /////////////////////////////////////////////////////////////////////////
    /**
     * Retrieve an external iterator
     * @link http://php.net/manual/en/iteratoraggregate.getiterator.php
     * @return Traversable An instance of an object implementing <b>Iterator</b> or
     * <b>Traversable</b>
     * @since 5.0.0
     */
    public function getIterator(): Traversable{
        $dir = new DirectoryIterator($this->uri);
        return new IteratorIterator($dir);
    }
}